<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

use App\Models\User;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Services\RegGenerator;
use App\Http\Requests\CheckOutRequest;
use App\Mail\OrderMail;
use App\Models\OrderPayment;
use App\Services\PointService;

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
}
