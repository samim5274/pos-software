<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

use App\Models\User;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Services\RegGenerator;
use App\Http\Requests\ConfirmOrderRequest;
use App\Http\Requests\CustomerOrderRequest;
use App\Http\Requests\CheckOutRequest;
use App\Http\Requests\ConfirmPaymentRequest;
use App\Services\PointService;
use App\Mail\OrderMail;
use App\Models\OrderPayment;


class AdminCartController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json([
                'message' => 'Unauthorized user',
            ], 401);
        }

        $reg = RegGenerator::generateOrderReg($userId);

        $items = Cart::with(['product.images','user'])
            ->where('user_id', $userId)
            ->where('reg', $reg)
            ->get();

        return response()->json([
            'message' => 'Cart items',
            'reg' => $reg,
            'data' => $items
        ], 200);
    }

    public function adminAddToCart(Request $request)
    {
        $data = $request->validate([
            'product_id'    => ['required', 'exists:products,id'],
            'quantity'      => ['nullable', 'integer', 'min:1'],
        ]);

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $requestedQty = (int) ($data['quantity'] ?? 1);



        try{
            return DB::transaction(function () use ($data, $user, $requestedQty) {

                $product = Product::lockForUpdate()->find($data['product_id']);
                if (!$product) {
                    throw ValidationException::withMessages([
                        'product_id' => 'Product not found.',
                    ]);
                }
                if (!$product->is_active) {
                    throw ValidationException::withMessages([
                        'product_id' => 'This product is currently inactive.',
                    ]);
                }
                if ((int) $product->stock_quantity <= 0) {
                    throw ValidationException::withMessages([
                        'product_id' => 'This product is out of stock.',
                    ]);
                }

                $reg = RegGenerator::generateOrderReg($user->id);
                if (!$reg) {
                    throw ValidationException::withMessages([
                        'reg' => 'Failed to generate cart session.',
                    ]);
                }

                $basePrice = (float) $product->price;
                $discountAmount = (float) ($product->discount ?? 0);
                $finalPrice = max(0, $basePrice - $discountAmount);

                // ======================
                // Cart item find
                // ======================
                $query = Cart::where('reg', $reg)->where('product_id', $product->id);

                $cartItem = $query->first();

                // ======================
                // Quantity logic
                // ======================
                $requestedQty = 1;
                $currentQty = $cartItem->quantity ?? 0;
                $newQty = $currentQty + $requestedQty;

                // ======================
                // Save cart
                // ======================
                if ($cartItem) {
                    $cartItem->update([
                        'quantity'          => $newQty,
                        'price'             => $basePrice,
                        'discount'          => $discountAmount,
                        'total_amount'    => $finalPrice,
                    ]);
                } else {
                    $cartItem = Cart::create([
                        'reg'               => $reg,
                        'user_id'           => $user->id,
                        'product_id'        => $product->id,
                        'quantity'          => $requestedQty,
                        'price'             => $basePrice,
                        'discount'          => $discountAmount,
                        'total_amount'    => $finalPrice,
                        'point'             => $product->point,
                    ]);
                }

                // ======================
                // RESPONSE (OUTSIDE EXCEPTION FLOW STYLE)
                // ======================
                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart successfully.',
                    'data' => [
                        'cart_id'    => $cartItem->id,
                        'product_id' => $product->id,
                        'quantity'   => $cartItem->quantity,
                        'price'      => (float) $finalPrice,
                        'total'      => (float) ($finalPrice * $cartItem->quantity)
                    ]
                ], 201);

            });
        } catch (\Exception $e) {
            Log::error('POS Add To Cart Failed', [
                'user_id'    => $user->id,
                'product_id' => $data['product_id'] ?? null,
                'quantity'   => $requestedQty,
                'message'    => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to add product to cart. Please try again.',
                // debug only
                // 'message' => $e->getMessage(),
                // 'file'    => $e->getFile(),
                // 'line'    => $e->getLine(),
            ], 500);
        }
    }

    public function adminAddToCartSearch(Request $request)
    {
        $data = $request->validate([
            'product'    => ['required', 'string'],
            'quantity'      => ['nullable', 'integer', 'min:1'],
        ]);

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $requestedQty = (int) ($data['quantity'] ?? 1);

        try{
            return DB::transaction(function () use ($data, $user, $requestedQty) {

                $search = trim($data['product']);

               $product = Product::lockForUpdate()
                    ->where(function ($query) use ($search) {
                        $query->where('id', $search)
                            ->orWhere('sku', $search)
                            ->orWhere('slug', $search)
                            ->orWhere('name', 'LIKE', "%{$search}%");
                    })
                    ->first();
                if (!$product) {
                    throw ValidationException::withMessages([
                        'product' => 'Product not found.',
                    ]);
                }
                if (!$product->is_active) {
                    throw ValidationException::withMessages([
                        'product' => 'This product is currently inactive.',
                    ]);
                }
                if ((int) $product->stock_quantity <= 0) {
                    throw ValidationException::withMessages([
                        'product' => 'This product is out of stock.',
                    ]);
                }

                $reg = RegGenerator::generateOrderReg($user->id);
                if (!$reg) {
                    throw ValidationException::withMessages([
                        'reg' => 'Failed to generate cart session.',
                    ]);
                }

                $basePrice = (float) $product->price;
                $discountAmount = (float) ($product->discount ?? 0);
                $finalPrice = max(0, $basePrice - $discountAmount);

                // ======================
                // Cart item find
                // ======================
                $query = Cart::where('reg', $reg)->where('product_id', $product->id);

                $cartItem = $query->first();

                // ======================
                // Quantity logic
                // ======================
                $requestedQty = 1;
                $currentQty = $cartItem->quantity ?? 0;
                $newQty = $currentQty + $requestedQty;

                // ======================
                // Save cart
                // ======================
                if ($cartItem) {
                    $cartItem->update([
                        'quantity'          => $newQty,
                        'price'             => $basePrice,
                        'discount'          => $discountAmount,
                        'total_amount'    => $finalPrice,
                    ]);
                } else {
                    $cartItem = Cart::create([
                        'reg'               => $reg,
                        'user_id'           => $user->id,
                        'product_id'        => $product->id,
                        'quantity'          => $requestedQty,
                        'price'             => $basePrice,
                        'discount'          => $discountAmount,
                        'total_amount'    => $finalPrice,
                        'point'             => $product->point,
                    ]);
                }

                // ======================
                // RESPONSE (OUTSIDE EXCEPTION FLOW STYLE)
                // ======================
                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart successfully.',
                    'data' => [
                        'cart_id'    => $cartItem->id,
                        'product_id' => $product->id,
                        'quantity'   => $cartItem->quantity,
                        'price'      => (float) $finalPrice,
                        'total'      => (float) ($finalPrice * $cartItem->quantity)
                    ]
                ], 201);

            });
        } catch (\Exception $e) {
            Log::error('POS Add To Cart Failed', [
                'user_id'    => $user->id,
                'product_id' => $data['product_id'] ?? null,
                'quantity'   => $requestedQty,
                'message'    => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to add product to cart. Please try again.',
                // debug only
                // 'message' => $e->getMessage(),
                // 'file'    => $e->getFile(),
                // 'line'    => $e->getLine(),
            ], 500);
        }
    }

    public function updateQty(Request $request, $reg, $product_id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $cartItem = Cart::where('reg', $reg)
            ->where('product_id', $product_id)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
            ], 404);
        }

        $cartItem->update([
            'quantity' => $request->quantity,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Qty updated successfully',
            'quantity' => $cartItem->quantity,
        ]);
    }

    public function removeToCart(Request $request, $cart_id, $reg, $product_id){

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        try{
            if (!$reg || !$product_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request data'
                ], 422);
            }

            $cartItem = Cart::where('id', $cart_id)
                ->where('user_id', $user->id)
                ->where('reg', $reg)
                ->where('product_id', $product_id)
                ->first();

            if (!$cartItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart item not found'
                ], 404);
            }

            $cartItem->delete();

            $remaining = Cart::where('user_id', $user->id)
                ->where('reg', $reg)
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart successfully',
                'remaining_items' => $remaining
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('Cart Remove Error', [
                'user_id' => $user->id,
                'cart_id' => $cart_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    public function getCartItem($reg)
    {
        try {
            $items = Cart::with(['product.images','user'])
                        ->where('reg', $reg)->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No cart items found.',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart items fetched successfully.',
                'reg' => $reg,
                'data' => $items
            ], 200);

        } catch (\Throwable $e) {

            \Log::error('Cart fetch error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching cart items.',
            ], 500);
        }
    }

    public function checkOut(CheckOutRequest $request, string $reg)
    {
        $validated = $request->validated();

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized user',
            ], 401);
        }

        try {
            $result = DB::transaction(function () use ($validated, $user, $reg, $request) {

                $cartItems = Cart::query()
                    ->where('reg', $reg)
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->get();

                if ($cartItems->isEmpty()) {
                    throw ValidationException::withMessages([
                        'cart' => ['Cart is empty or checkout has already been completed.'],
                    ]);
                }

                $customerPhone = $validated['phone_number'] ?? null;
                $customerName  = isset($validated['customer_name']) ? trim($validated['customer_name']) : null;

                $customer = null;

                if ($customerPhone) {
                    $customer = Customer::firstOrCreate(
                        ['phone' => $customerPhone],
                        ['customer_name' => $customerName ?? 'Walk-in Customer']
                    );

                    if ($customerName && blank($customer->customer_name)) {
                        $customer->update(['customer_name' => $customerName]);
                    }
                }

                $subtotal = round($cartItems->sum(fn ($item) => (float) $item->price * (int) $item->quantity), 2);

                $cartDiscount = round($cartItems->sum(fn ($item) => (float) ($item->discount ?? 0) * (int) $item->quantity), 2);

                $manualDiscount = round(max(0, (float) ($validated['discount'] ?? 0)), 2);

                $discount = round(min($subtotal, $cartDiscount + $manualDiscount), 2);

                // ---- Percentage-based VAT ----
                $vatPercentage = round(min(100, max(0, (float) ($validated['vat'] ?? 0))), 2);

                $taxableAmount = round(max(0, $subtotal - $discount), 2);

                $vat = round(($taxableAmount * $vatPercentage) / 100, 2);
                // -------------------------------

                $payableAmount = round(max(0, $taxableAmount + $vat), 2);

                $receivedAmount = round(max(0, (float) ($validated['received_amount'] ?? 0)), 2);
                $changeAmount   = round(max(0, $receivedAmount - $payableAmount), 2);
                $paidAmount     = round(min($receivedAmount, $payableAmount), 2);

                if ($payableAmount > 0 && $paidAmount <= 0) {
                    throw ValidationException::withMessages([
                        'received_amount' => ['Payment amount must be greater than zero.'],
                    ]);
                }

                $isPartiallyPaid = $paidAmount < $payableAmount;
                // --------------------------------------------------------------
                if ($isPartiallyPaid) {
                    if (!$customerPhone) {
                        throw ValidationException::withMessages([
                            'phone_number' => ['Customer phone number is required for partial payments.'],
                        ]);
                    }

                    if (!$customerName) {
                        throw ValidationException::withMessages([
                            'customer_name' => ['Customer name is required for partial payments.'],
                        ]);
                    }
                }
                // --------------------------------------------------------------

                $paymentMethod = $validated['payment_method'] ?? OrderPayment::METHOD_CASH;

                if (!in_array($paymentMethod, OrderPayment::PAYMENT_METHODS, true)) {
                    throw ValidationException::withMessages([
                        'payment_method' => ['Invalid payment method.'],
                    ]);
                }

                $point = (int) $cartItems->sum(fn ($item) => (int) ($item->point ?? 0) * (int) $item->quantity);

                $orderStatus = Order::STATUS_COMPLETED;

                do {
                    $slug = Str::slug(
                        'order-' . $reg . '-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(6))
                    );
                } while (Order::where('slug', $slug)->exists());

                $orderNumber = 'ORD-' . now()->format('Ymd') . '-' . str_pad(
                    (string) (Order::whereDate('created_at', now()->toDateString())->count() + 1), 4, '0', STR_PAD_LEFT
                );

                $order = Order::create([
                    'reg'            => $reg,
                    'order_number'   => $orderNumber,
                    'slug'           => $slug,
                    'order_date'     => now()->toDateString(),
                    'user_id'        => $user->id,
                    'customer_id'    => $customer?->id,
                    'customer_name'  => $customerName,
                    'customer_phone' => $customerPhone,
                    'subtotal'       => $subtotal,
                    'discount'       => $discount,
                    'vat_percentage' => $vatPercentage,
                    'vat'            => $vat,
                    'due_amount'     => round(max(0, $payableAmount - $paidAmount), 2),
                    'payable_amount' => $payableAmount,
                    'payment_method' => $paymentMethod,
                    'currency'       => Order::CURRENCY_BDT,
                    'point'          => $point,
                    'status'         => $orderStatus,
                    'completed_at'   => $orderStatus === Order::STATUS_COMPLETED ? now() : null,
                    'remarks'        => $validated['remarks'] ?? "Order created by user: {$user->name}",
                    'ip_address'     => $request->ip(),
                    'user_agent'     => $request->userAgent(),
                ]);

                $payment = null;

                if ($paidAmount > 0) {
                    $payment = OrderPayment::create([
                        'order_id'       => $order->id,
                        'user_id'        => $user->id,
                        'customer_id'    => $customer->id,
                        'received_by'    => $user->id,
                        'payment_type'   => OrderPayment::TYPE_PAYMENT,
                        'payment_method' => $paymentMethod,
                        'amount'         => $paidAmount,
                        'currency'       => OrderPayment::CURRENCY_BDT,
                        'paid_at'        => now(),
                        'remarks'        => $validated['remarks'] ?? "Order payment received by user: {$user->name}",
                        'ip_address'     => $request->ip(),
                        'user_agent'     => $request->userAgent(),
                    ]);
                }

                return [
                    'order'         => $order,
                    'payment'       => $payment,
                    'change_amount' => $changeAmount,
                ];
            }, 3);

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully.',
                'data'    => [
                    'order'         => $result['order'],
                    'payment'       => $result['payment'],
                    'change_amount' => $result['change_amount'],
                ],
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Order confirmation failed', [
                'user_id' => $user?->id,
                'reg'     => $reg,
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => app()->isProduction()
                    ? 'Something went wrong. Please try again.'
                    : $e->getMessage(),
            ], 500);
        }
    }

}
