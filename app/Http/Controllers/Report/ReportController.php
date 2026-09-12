<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

use App\Models\Product;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Supplyer;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderPayment;
use App\Models\Expense;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Cart::query()
            ->with(['product:id,name,sku'])
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('COUNT(*) as sale_count'),
                DB::raw('SUM(price * quantity) as total_price'),
                DB::raw('SUM(discount * quantity) as total_discount'),
                DB::raw('SUM((price - discount) * quantity) as total_amount')
            )
            ->groupBy('product_id')
            ->orderByDesc('total_qty');

        // Date filter
        if ($request->filled('from_date')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->from_date
            );
        }

        if ($request->filled('to_date')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->to_date
            );
        }

        $reports = $query->paginate(
            $request->integer('per_page', 20)
        );

        return response()->json([
            'status' => true,
            'message' => 'Product report fetched successfully.',

            'data' => $reports->items(),

            'pagination' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
                'from' => $reports->firstItem(),
                'to' => $reports->lastItem(),
            ],
        ]);
    }


    public function expense(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date'      => 'nullable|date|date_format:Y-m-d',
            'end_date'        => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
            'category_id'     => 'nullable|integer|exists:ex_categories,id',
            'sub_category_id' => 'nullable|integer|exists:ex_sub_categories,id',
            'user_id'         => 'nullable|integer|exists:users,id',
            'search'          => 'nullable|string|max:100',
            'per_page'        => 'nullable|integer|min:10|max:100',
        ]);

        $perPage = $validated['per_page'] ?? 20;

        $query = Expense::with([
            'category:id,name',
            'subcategory:id,name',
            'user:id,name',
        ]);

        if (!empty($validated['start_date'])) {
            $query->whereDate('date', '>=', $validated['start_date']);
        }

        if (!empty($validated['end_date'])) {
            $query->whereDate('date', '<=', $validated['end_date']);
        }

        if (!empty($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (!empty($validated['sub_category_id'])) {
            $query->where('sub_category_id', $validated['sub_category_id']);
        }

        if (!empty($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        }

        if (!empty($validated['search'])) {
            $search = trim($validated['search']);

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('remark', 'like', "%{$search}%");
            });
        }

        $summaryQuery = clone $query;

        $totalExpense = (clone $summaryQuery)->sum('amount');
        $totalTransactions = (clone $summaryQuery)->count();
        $averageExpense = $totalTransactions ? $totalExpense / $totalTransactions : 0;

        $categoryWise = (clone $summaryQuery)
            ->selectRaw('category_id, COUNT(*) as transaction_count, SUM(amount) as total_amount')
            ->with('category:id,name')
            ->groupBy('category_id')
            ->orderByDesc('total_amount')
            ->get()
            ->map(fn($item) => [
                'category_id'       => $item->category_id,
                'category'          => $item->category?->name ?? 'Uncategorized',
                'transaction_count' => (int) $item->transaction_count,
                'total_amount'      => number_format($item->total_amount, 2, '.', ''),
            ])
            ->values();

        $dailyWise = (clone $summaryQuery)
            ->selectRaw('DATE(date) as expense_date, COUNT(*) as transaction_count, SUM(amount) as total_amount')
            ->groupByRaw('DATE(date)')
            ->orderByDesc('expense_date')
            ->get()
            ->map(fn($item) => [
                'date'              => $item->expense_date,
                'transaction_count' => (int) $item->transaction_count,
                'total_amount'      => number_format($item->total_amount, 2, '.', ''),
            ])
            ->values();

        $expenses = $query
            ->select([
                'id',
                'category_id',
                'sub_category_id',
                'user_id',
                'title',
                'date',
                'amount',
                'remark',
                'created_at',
            ])
            ->latest('date')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'status' => true,
            'message' => 'Expense report retrieved successfully.',
            'data' => [
                'summary' => [
                    'total_expense'     => number_format($totalExpense, 2, '.', ''),
                    'total_transactions'=> $totalTransactions,
                    'average_expense'   => number_format($averageExpense, 2, '.', ''),
                ],
                'category_wise' => $categoryWise,
                'daily_wise'    => $dailyWise,
                'expenses'      => $expenses,
            ],
        ]);
    }

    public function customerDue(Request $request): JsonResponse
    {
        try {

            $perPage = min(
                max((int) $request->input('per_page', 20), 1),
                100
            );

            $search = trim((string) $request->input('search', ''));

            $query = Order::query()
                ->join('customers', 'customers.id', '=', 'orders.customer_id')
                ->select([
                    'customers.id',
                    'customers.customer_name AS name',
                    'customers.phone',
                ])
                ->selectRaw('SUM(orders.payable_amount) AS total_payable')
                ->selectRaw('SUM(orders.due_amount) AS total_due')
                ->whereNotNull('orders.customer_id')
                ->whereIn('orders.status', [
                    Order::STATUS_UNPAID,
                    Order::STATUS_PARTIALLY_PAID,
                ])
                ->where('orders.due_amount', '>', 0)
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('customers.customer_name', 'like', "%{$search}%")
                            ->orWhere('customers.phone', 'like', "%{$search}%");
                    });
                })
                ->groupBy('customers.id', 'customers.customer_name', 'customers.phone')
                ->orderByDesc('total_due');

            $customers = $query->paginate($perPage)->withQueryString();

            $grandTotalDue = Order::query()
                ->whereNotNull('customer_id')
                ->whereIn('status', [
                    Order::STATUS_UNPAID,
                    Order::STATUS_PARTIALLY_PAID,
                ])
                ->where('due_amount', '>', 0)
                ->sum('due_amount');

            return response()->json([
                'success' => true,
                'message' => 'Customer due report retrieved successfully.',
                'data' => $customers,
                'total_due' => round((float) $grandTotalDue, 2),
                'total_customers' => $customers->total(),
            ]);

        } catch (\Throwable $e) {

            Log::error('Customer Due Report Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customer due report.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function supplyerDue(Request $request): JsonResponse
    {
        try {

            $perPage = min(
                max((int) $request->input('per_page', 20), 1),
                100
            );

            $search = trim(
                (string) $request->input('search', '')
            );

            $query = Supplyer::query()
                ->leftJoin(
                    'purchase_orders',
                    function ($join) {

                        $join->on(
                            'purchase_orders.supplier_id',
                            '=',
                            'supplyers.id'
                        );

                        $join->whereIn(
                            'purchase_orders.status',
                            [
                                PurchaseOrder::STATUS_UNPAID,
                                PurchaseOrder::STATUS_PARTIALLY_PAID,
                            ]
                        );

                        $join->where(
                            'purchase_orders.due_amount',
                            '>',
                            0
                        );

                    }
                )
                ->select([
                    'supplyers.id',
                    'supplyers.name',
                    'supplyers.phone',
                ])
                ->selectRaw('COALESCE(SUM(purchase_orders.payable_amount), 0) AS total_payable')
                ->selectRaw('COALESCE(SUM(purchase_orders.due_amount), 0) AS total_due')
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('supplyers.name', 'like', "%{$search}%")
                            ->orWhere('supplyers.phone', 'like', "%{$search}%");
                    });
                })
                ->groupBy(
                    'supplyers.id',
                    'supplyers.name',
                    'supplyers.phone'
                )
                ->having('total_due', '>', 0)
                ->orderByDesc('total_due');

            $suppliers = $query
                ->paginate($perPage)
                ->withQueryString();


            $grandTotalDue = PurchaseOrder::query()
                ->whereNotNull('supplier_id')
                ->whereIn(
                    'status',
                    [
                        PurchaseOrder::STATUS_UNPAID,
                        PurchaseOrder::STATUS_PARTIALLY_PAID,
                    ]
                )
                ->where('due_amount', '>', 0)
                ->sum('due_amount');



            return response()->json([
                'success' => true,
                'message' => 'Supplier due report retrieved successfully.',
                'data' => $suppliers,
                'total_due' => round(
                    (float) $grandTotalDue,
                    2
                ),
                'total_suppliers' =>
                    (int) $suppliers->total(),

            ]);

        } catch (\Throwable $e) {

            Log::error(
                'Supplier Due Report Error',
                [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve supplier due report.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
