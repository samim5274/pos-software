<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

use App\Http\Requests\ConfirmOrderRequest;
use App\Http\Requests\ConfirmPaymentRequest;
use App\Models\User;
use App\Models\Order;
use App\Models\PointTransaction;
use App\Services\PointService;
use App\Mail\OrderMail;
use App\Models\Cart;
use App\Models\DeliveryChargePayment;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\PoliceStation;
use App\Models\ShippingZone;
use App\Models\CustomerAddress;
use App\Models\Transaction;
use App\Models\OrderPayment;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Notice;

class OrderController extends Controller
{
    protected $pointService;

    public function __construct(PointService $pointService)
    {
        $this->pointService = $pointService;
    }

    private function generateTransactionId(): string
    {
        do {
            $transactionId = 'TXN-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(10));
        } while (
            Transaction::where('transaction_id', $transactionId)->exists()
        );

        return $transactionId;
    }

    public function index()
    {
        try{
            $orders = Order::with(['user:id,name,user_id'])
                ->where('order_date', today())
                ->latest('id')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Orders fetched successfully.',
                'data' => $orders,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders. Please try again later.',
            ], 500);
        }
    }

    public function statusFilter(Request $request)
    {
        try{
            $query = Order::query()->with('user');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {

                $search = trim($request->search);

                $query->where(function ($q) use ($search) {

                    $q->where('reg', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");

                    // transaction_id column
                    if (\Schema::hasColumn('orders', 'transaction_id')) {
                        $q->orWhere('transaction_id', 'like', "%{$search}%");
                    }

                    $q->orWhereHas('user', function ($user) use ($search) {

                        $user->where('name', 'like', "%{$search}%");

                        // user_id column
                        if (\Schema::hasColumn('users', 'user_id')) {
                            $user->orWhere('user_id', 'like', "%{$search}%");
                        }

                    });

                });
            }

            $orders = $query->latest()->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function getOrderDetails($reg)
    {
        try{
            $user = auth()->user();

            $order = Order::with([
                    'user:id,name,user_id',
                    'customer:id, customer_name, phone, address'
                ])
                ->where('reg', $reg)
                ->first();

            if (! $order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.',
                    'data'    => null,
                ], 404);
            }

            $orderPayment = null;

            $orderPayment = OrderPayment::with(['order','user'])->where('order_id', $order->id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Order fetched successfully.',
                'data' => [
                    'order' => $order,
                    'payment' => $orderPayment,
                    'user' => $user,
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);

            // return response()->json([
            //     'success' => false,
            //     'message' => 'Something went wrong while fetching order details.',
            // ], 500);
        }
    }

    public function updateStatus(Request $request, $reg)
    {
        try {
            $statusInput = trim($request->status);

            $validStatuses = [
                'pending'          => 'Pending',
                'confirmed'        => 'Confirmed',
                'processing'       => 'Processing',
                'picked'           => 'Picked',
                'shipped'          => 'Shipped',
                'out for delivery' => 'Out for Delivery',
                'delivered'        => 'Delivered',
                'cancelled'        => 'Cancelled',
                'failed'           => 'Failed',
                'returned'         => 'Returned',
            ];

            $lowerInput = strtolower($statusInput);
            $normalizedStatus = $validStatuses[$lowerInput] ?? $statusInput;

            $request->merge(['status' => $normalizedStatus]);

            $validated = $request->validate([
                'status' => [
                    'required',
                    'string',
                    'in:' . implode(',', array_values($validStatuses))
                ]
            ]);

            $order = Order::where('reg', $reg)->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.',
                ], 404);
            }

            if ($order->status === $validated['status']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order status is already ' . $order->status,
                ], 200);
            }

            DB::beginTransaction();

            $currentStatus = $validated['status'];
            $statusKey = strtolower($currentStatus);

            $timestampMapping = [
                'confirmed' => 'confirmed_at',
                'shipped'   => 'shipped_at',
                'delivered' => 'delivered_at',
                'cancelled' => 'cancelled_at',
            ];

            $updateData = ['status' => $currentStatus];

            if (isset($timestampMapping[$statusKey])) {
                $column = $timestampMapping[$statusKey];
                $updateData[$column] = now()->toDateString();
            }

            // if ($statusKey === 'delivered') {
            //     $updateData['paid_at'] = now()->toDateString();
            //     $updateData['payment_status'] = "Paid";
            // }

            $order->update($updateData);

            // if ($statusKey === 'delivered') {
            //     $exists = PointTransaction::where('reference_id', $order->reg)
            //         ->where('source', 'purchase')
            //         ->exists();

            //     if (!$exists) {
            //         PointTransaction::create([
            //             'user_id'        => $order->user_id,
            //             'type'           => 'earn',
            //             'points'         => (int) $order->point,
            //             'bonus_amount'   => 0,
            //             'bonus_status'   => 'credit',
            //             'source'         => 'purchase',
            //             'reference_id'   => $order->reg,
            //             'note'           => 'Points added for delivered order',
            //         ]);
            //     }
            // }

            if ($statusKey === 'delivered') {
                $user = User::find($order->user_id);
                if (!$user) return;

                $exists = PointTransaction::where('reference_id', $order->reg)
                    ->where('source', 'purchase')
                    ->exists();

                if (!$exists) {
                    if ($order->point > 0) {
                        $this->pointService->distributeOrderPoints($user, (int)$order->point, $order->reg);
                    }
                }

                Notice::create([
                    'title'        => "Order Delivered - {$order->reg}",
                    'description'  => 'Your order has been delivered successfully. Please share your valuable review and feedback. Thank you for choosing us.',
                    'publish_date' => Carbon::now(),
                    'user_id'      => $order->user_id,
                    'notice_type'  => 'Order_Update',
                ]);

                // referral bonus always safe guarded inside service
                $this->pointService->referralBonus($user, $order->reg, (int)$order->point);
            }

            $statusTitle = "Order {$currentStatus} - {$order->reg}";

            $statusDescription = match ($statusKey) {
                'confirmed'         => "Your order has been confirmed and will be processed shortly.",
                'processing'        => "Your order is currently being processed.",
                'picked'            => "Your order has been picked and is being prepared for shipment.",
                'shipped'           => "Your order has been shipped and is on its way.",
                'out for delivery'  => "Your order is out for delivery and will arrive soon.",
                'delivered'         => "Your order has been delivered successfully. Thank you for shopping with us.",
                'cancelled'         => "Your order has been cancelled.",
                'returned'          => "Your order has been returned successfully.",
                'failed'            => "Unfortunately, your order could not be completed.",
                default             => "Your order status has been updated to {$currentStatus}.",
            };

            Notice::create([
                'title'        => $statusTitle,
                'description'  => $statusDescription,
                'publish_date' => Carbon::now(),
                'user_id'      => $order->user_id,
                'notice_type'  => 'Order_Update',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully.',
                'data' => $order->fresh()
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status selected.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ORDER STATUS ERROR', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error. Please try again.',
            ], 500);
        }
    }

    public function getCustomerDetails($user_id){
        try{
            $customer = User::where('user_id', $user_id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Customer Details fetched successfully.',
                'data' => $customer,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch customer details. Please try again later.',
            ], 500);
        }
    }

    public function reportSale()
    {
        try{
            $orders = Order::with('user')->latest()->paginate(20);

            $totalAmount = Order::sum('amount');

            return response()->json([
                'success' => true,
                'message' => 'Orders fetched successfully.',
                'data' => $orders,
                'total_amount' => (float) $totalAmount,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders. Please try again later.',
            ], 500);
        }
    }

    public function reportSaleFilter(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date'   => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        try{

            $startDate = $request->start_date ?? now()->startOfDay()->toDateString();
            $endDate   = $request->end_date ?? now()->endOfDay()->toDateString();

            $query = Order::whereBetween('date', [$startDate, $endDate]);

            $totalAmount = (clone $query)->sum('amount');

            $orders = $query->with('user:id,user_id,name,email')
                ->latest()
                ->paginate(20);

            return response()->json([
                'success' => true,
                'message' => 'Orders fetched successfully.',
                'data'    => $orders,
                'total_amount' => (float) $totalAmount,
            ], 200);
        } catch (\Throwable $e) {


            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders. Please try again later.',
            ], 500);
        }
    }

    public function verifyPayment(Request $request, int $paymentId): JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        try {

            $orderPayment = OrderPayment::find($paymentId);

            if (! $orderPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment record not found.',
                ], 404);
            }

            if ($orderPayment->status === OrderPayment::STATUS_SUCCESS) {
                return response()->json([
                    'success' => false,
                    'message' => 'This payment has already been verified.',
                ], 422);
            }

            $order = Order::find($orderPayment->order_id);

            if (! $order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.',
                ], 404);
            }

            DB::transaction(function () use ($request, $orderPayment, $order, $user)
            {
                $orderPayment->update([
                    'status'      => OrderPayment::STATUS_SUCCESS,
                    'verified_by' => $user->id,
                    'verified_at' => now(),
                    'remarks'     => 'Advance payment submitted. An admin payment verified.',
                    'ip_address'  => $request->ip(),
                    'user_agent'  => $request->userAgent(),
                ]);

                $order->update([
                    'payment_status' => Order::PAYMENT_PAID,
                    'paid_amount'    => $order->payable_amount,
                    'due_amount'     => ($orderPayment->net_amount - $order->payable_amount),
                    'paid_at'        => now(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully.',
                'data' => [
                    'payment_id' => $orderPayment->id,
                    'order_id'   => $order->id,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Payment verification failed.', [
                'payment_id' => $paymentId,
                'user_id'    => $user->id,
                'message'    => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to verify payment. Please try again later.',
            ], 500);
        }
    }

    public function confirmPayment(ConfirmPaymentRequest $request, $reg)
    {
        $validated = $request->validated();

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized user',
            ], 401);
        }

        try
        {
            $payment = DB::transaction(function () use ($validated, $user, $request, $reg) {

                $order = Order::where('reg', $reg)->lockForUpdate()->first();

                if (! $order) {
                    throw ValidationException::withMessages([
                        'order' => ['Order not found.'],
                    ]);
                }

                if ($order->payment_status === Order::PAYMENT_PAID) {
                    throw ValidationException::withMessages([
                        'order' => ['This order has already been paid.'],
                    ]);
                }

                if ($validated['amount'] <= 0) {
                    throw ValidationException::withMessages([
                        'amount' => ['Invalid payment amount.'],
                    ]);
                }

                // Re-check inside the transaction to close the race window
                // between the earlier read and this write.
                if (! empty($validated['transaction_id'])) {
                    $duplicate = OrderPayment::where('transaction_id', $validated['transaction_id'])
                        ->lockForUpdate()
                        ->exists();

                    if ($duplicate) {
                        throw ValidationException::withMessages([
                            'transaction_id' => [
                                'Transaction ID already exists.'
                            ],
                        ]);
                    }
                }

                $paidAmount = $order->payments()->success()->sum('net_amount');
                $remaining = $order->payable_amount - $paidAmount;

                if ($validated['amount'] > $remaining) {
                    throw ValidationException::withMessages([
                        'amount' => ['Payment exceeds due amount.'],
                    ]);
                }

                do {
                    $receiptNo = 'RCPT-' . Str::upper(Str::random(10));
                } while (OrderPayment::where('receipt_no', $receiptNo)->exists());

                $provider = match ($validated['payment_method']) {
                    OrderPayment::METHOD_CASH
                        => OrderPayment::PROVIDER_CASH,
                    OrderPayment::METHOD_BANK_TRANSFER
                        => OrderPayment::PROVIDER_BANK,
                    OrderPayment::METHOD_MOBILE_BANKING
                        => OrderPayment::PROVIDER_MANUAL,
                    OrderPayment::METHOD_CARD
                        => OrderPayment::PROVIDER_STRIPE,
                    OrderPayment::METHOD_PAYPAL
                        => OrderPayment::PROVIDER_PAYPAL,
                    default
                        => OrderPayment::PROVIDER_MANUAL,
                };

                if( $validated['payment_method'] === OrderPayment::METHOD_MOBILE_BANKING)
                {
                    $bankName           = $validated['provider'];
                    $accountNumber      = $validated['sender_mobile'] ?? null;
                    $accountHolderName  = $validated['sender_name'] ?? null;
                } else
                {
                    $bankName           = $validated['bank_name'] ?? null;
                    $accountNumber      = $validated['account_number'] ?? null;
                    $accountHolderName  = $validated['account_holder_name'] ?? null;
                }

                // Order Payment Table
                $orderPayment = OrderPayment::create([
                    'order_id'                  => $order->id,
                    'user_id'                   => $user->id,

                    'payment_method'            => $validated['payment_method'],
                    'provider'                  => $provider,
                    'payment_type'              => OrderPayment::TYPE_PAYMENT,

                    // Manual verification required
                    'channel'                   => OrderPayment::getChannel($validated['payment_method']),

                    // Transaction
                    'transaction_id'            => $validated['transaction_id'] ?? null,
                    'bank_name'                 => $bankName,
                    'account_number'            => $accountNumber,
                    'account_holder_name'       => $accountHolderName,
                    'sender_mobile'             => $validated['sender_mobile'] ?? null,
                    'sender_name'               => $validated['sender_name'] ?? null,

                    // Amount
                    'amount'                    => $validated['amount'],
                    'gateway_fee'               => 0,
                    'net_amount'                => $validated['amount'],
                    'currency'                  => OrderPayment::CURRENCY_BDT,

                    // Status
                    'status'                    => OrderPayment::STATUS_SUCCESS,
                    'paid_at'                   => now(),

                    // Security
                    'ip_address'                => $request->ip(),
                    'user_agent'                => $request->userAgent(),
                    'receipt_no'                => $receiptNo,

                    'verified_by'               => $user->id,
                    'received_by'               => $user->id,
                    'verified_at'               => now(),
                    'remarks'                   => $validated['remarks'] ?? null,
                ]);

                $newPaidAmount = $paidAmount + $validated['amount'];
                $newDueAmount = max($order->payable_amount - $newPaidAmount, 0);
                $paymentStatus = $newDueAmount <= 0
                    ? Order::PAYMENT_PAID
                    : Order::PAYMENT_PARTIAL;

                $order->update([
                    'payment_status' => $paymentStatus,
                    'paid_amount'    => $newPaidAmount,
                    'due_amount'     => $newDueAmount,
                    'paid_at'        => $paymentStatus === Order::PAYMENT_PAID ? now() : null,
                ]);

                // Delivery payment
                DeliveryChargePayment::create([
                        'order_id'            => $order->id,

                        'payment_date'        => now(),
                        'payment_method'      => $validated['payment_method'], // migration enum: bank | mobile | sslcommerz | cash

                        'amount'              => $order->shipping_charge,
                        'currency'            => 'BDT',

                        // bank_name column এ mobile banking provider (Bkash/Nagad/Rocket)
                        'bank_name'           => $bankName,
                        'branch_name'         => null, // frontend এ এই field নেই
                        'account_number'      => $accountNumber,
                        'mobile_number'       => $validated['sender_mobile'] ?? null,
                        'account_holder_name' => $accountHolderName,
                        'transaction_id'      => $validated['transaction_id'] ?? null,
                        'reference_no'        => $validated['remarks'] ?? null,

                        'payment_status'      => 'success', // admin manually verify
                        'paid_by'             => $user->id,

                        'notes'               => 'Delivery charge submitted by admin order by Cash on delivery',
                    ]);

                return $orderPayment;
            });

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully.',
                'data' => [
                    'payment' => $payment,
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {

            Log::error('Payment submission failed.', [
                'order_reg'=>$reg,
                'user_id'=>$user->id,
                'amount'=>$validated['amount'] ?? null,
                'payment_method'=>$validated['payment_method'] ?? null,
                'transaction_id'=>$validated['transaction_id'] ?? null,
                'message'=>$e->getMessage(),
                'file'=>$e->getFile(),
                'line'=>$e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => app()->isProduction()
                    ? 'Unable to process payment.'
                    : $e->getMessage(),
            ], 500);
        }
    }

    public function deliveryStatusUpdate(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,success,return,failed',
        ]);

        try {
            $deliveryCharge = DeliveryChargePayment::find($id);

            if (! $deliveryCharge) {
                return response()->json([
                    'success' => false,
                    'message' => 'Delivery charge payment not found.',
                    'data'    => null,
                ], 404);
            }

            $updateData = [
                'payment_status' => $request->payment_status,
            ];

            if ($request->payment_status === 'success' && $deliveryCharge->payment_status !== 'success') {
                $updateData['paid_by'] = auth()->id();
                $updateData['payment_date'] = now();
            }

            $deliveryCharge->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Delivery charge status updated successfully.',
                'data'    => $deliveryCharge->fresh('paidBy'),
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Failed to update delivery charge status.', [
                'id'    => $id,
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating delivery charge status.',
            ], 500);
        }
    }
}
