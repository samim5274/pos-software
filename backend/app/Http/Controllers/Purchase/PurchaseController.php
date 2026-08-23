<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

use App\Models\User;
use App\Models\Product;
use App\Models\Stock;
use App\Models\PurchaseCart;
use App\Models\PurchaseOrder;
use App\Models\Supplyer;
use App\Models\PurchaseOrderPayment;
use App\Services\PurchaseRegGenerator;
use App\Http\Requests\CheckOutPurchaseOrderRequest;

class PurchaseController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json([
                'message' => 'Unauthorized user',
            ], 401);
        }

        $reg = PurchaseRegGenerator::generateOrderReg($userId);

        $items = PurchaseCart::with(['product.images','user'])
            ->where('user_id', $userId)
            ->where('reg', $reg)
            ->get();

        $supplyers = Supplyer::select([
            'id',
            'name',
            'company_name',
            'phone',
            'code',
        ])->orderBy('name')->get();

        return response()->json([
            'message' => 'Cart items',
            'reg' => $reg,
            'data' => [
                'items' => $items,
                'supplyers' => $supplyers,
            ],
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

                $reg = PurchaseRegGenerator::generateOrderReg($user->id);
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
                $query = PurchaseCart::where('reg', $reg)->where('product_id', $product->id);

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
                    $cartItem = PurchaseCart::create([
                        'reg'               => $reg,
                        'user_id'           => $user->id,
                        'product_id'        => $product->id,
                        'quantity'          => $requestedQty,
                        'price'             => $basePrice,
                        'discount'          => $discountAmount,
                        'total_amount'      => $finalPrice,
                        'point'             => $product->point,
                    ]);


                }

                $stock = Stock::where('reg', $reg)->where('product_id', $product->id )->first();

                if($stock) {
                    $stock->update([
                        'stockIn' => $newQty,
                    ]);
                } else {
                    Stock::Create([
                        'reg' => $reg,
                        'date' => now()->toDateString(),
                        'product_id' => $product->id,
                        'stockIn' => $newQty,
                        'remark' => 'add to cart by : '.  $user->name,
                    ]);
                }

                // if($product){
                //     $product->stock_quantity = $product->stock_quantity - $requestedQty;
                //     $product->update();
                // }

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

                $reg = PurchaseRegGenerator::generateOrderReg($user->id);
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
                $query = PurchaseCart::where('reg', $reg)->where('product_id', $product->id);

                $cartItem = $query->first();

                // ======================
                // Quantity logic
                // ======================
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
                    $cartItem = PurchaseCart::create([
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

                $stock = Stock::where('reg', $reg)->where('product_id', $product->id )->first();

                if($stock) {
                    $stock->update([
                        'stockIn' => $newQty,
                    ]);
                } else {
                    Stock::Create([
                        'reg' => $reg,
                        'date' => now()->toDateString(),
                        'product_id' => $product->id,
                        'stockIn' => $newQty,
                        'remark' => 'add to cart by : '.  $user->name,
                    ]);
                }

                // if($product){
                //     $product->stock_quantity = $product->stock_quantity - $requestedQty;
                //     $product->update();
                // }

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

        try {
            return DB::transaction(function () use ($request, $reg, $product_id) {

                $cartItem = PurchaseCart::where('reg', $reg)
                    ->where('product_id', $product_id)
                    ->lockForUpdate()
                    ->first();

                if (!$cartItem) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cart item not found',
                    ], 404);
                }

                $oldQty = $cartItem->quantity;
                $newQty = $request->quantity;

                // Quantity difference
                $difference = $newQty - $oldQty;

                // Update cart quantity
                $cartItem->update([
                    'quantity' => $newQty,
                ]);

                // Update stock record
                $stock = Stock::where('reg', $reg)
                    ->where('product_id', $product_id)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $stock->update([
                        'stockIn' => $newQty,
                    ]);
                }

                // Update product stock
                $product = Product::lockForUpdate()->find($product_id);

                if ($product && $difference != 0) {

                    if($product->stock_quantity <= 0) {
                        throw ValidationException::withMessages([
                            'quantity' => "Only {$product->stock_quantity} items are available in stock.",
                        ]);
                    }

                    // if ($difference > 0) {
                    //     // Cart quantity increased
                    //     $product->increment(
                    //         'stock_quantity',
                    //         $difference
                    //     );

                    // } else {
                    //     // Cart quantity decreased
                    //     $product->increment(
                    //         'stock_quantity',
                    //         abs($difference)
                    //     );
                    // }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Qty updated successfully',
                    'quantity' => $newQty,
                ]);
            });

        } catch (\Throwable $e) {

            \Log::error('Cart Qty Update Error', [
                'reg' => $reg,
                'product_id' => $product_id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function removeToCart(Request $request, $cart_id, $reg, $product_id)
    {

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        try{
            return DB::transaction(function () use ($cart_id, $reg, $product_id, $user) {

                if (!$reg || !$product_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid request data'
                    ], 422);
                }

                $cartItem = PurchaseCart::where('id', $cart_id)
                    ->where('user_id', $user->id)
                    ->where('reg', $reg)
                    ->where('product_id', $product_id)
                    ->lockForUpdate()
                    ->first();

                if (!$cartItem) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cart item not found'
                    ], 404);
                }

                $cartItem->delete();

                $stock = Stock::where('reg', $reg)
                    ->where('product_id', $product_id)
                    ->first();

                if ($stock) {
                    $stock->delete();
                }

                $product = Product::lockForUpdate()->find($product_id);

                // if($product){
                //     $product->decrement('stock_quantity', $cartItem->quantity);
                // }

                $remaining = PurchaseCart::where('user_id', $user->id)
                    ->where('reg', $reg)
                    ->count();

                return response()->json([
                    'success' => true,
                    'message' => 'Item removed from cart successfully',
                    'remaining_items' => $remaining
                ], 200);
            });

        } catch (\Throwable $e) {
            Log::error('Cart Remove Error', [
                'user_id'    => $user->id,
                'cart_id'    => $cart_id,
                'reg'        => $reg,
                'product_id' => $product_id,
                'error'      => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
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
            $items = PurchaseCart::with(['product.images','user'])
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

    public function confirmOrder(CheckOutPurchaseOrderRequest $request, string $reg)
    {
        $validated = $request->validated();
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized user.',
            ], 401);
        }

        try {
            $result = DB::transaction(function () use ($validated, $user, $reg, $request) {
                $cartItems = PurchaseCart::query()
                    ->where('reg', $reg)
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->get();

                if ($cartItems->isEmpty()) {
                    throw ValidationException::withMessages([
                        'cart' => ['Cart is empty or checkout has already been completed.'],
                    ]);
                }

                $supplier = null;
                $supplierId = $validated['supplier_id'] ?? null;
                $supplierName = isset($validated['supplier_name']) ? trim($validated['supplier_name']) : null;
                $supplierPhone = isset($validated['supplier_phone']) ? trim($validated['supplier_phone']) : null;

                if ($supplierId) {
                    $supplier = Supplyer::query()
                        ->whereKey($supplierId)
                        ->lockForUpdate()
                        ->first();

                    if (!$supplier) {
                        throw ValidationException::withMessages([
                            'supplier_id' => ['Selected supplier does not exist.'],
                        ]);
                    }
                } elseif ($supplierPhone) {
                    $supplier = Supplyer::query()
                        ->where('phone', $supplierPhone)
                        ->lockForUpdate()
                        ->first();

                    if (!$supplier) {
                        if (!$supplierName) {
                            throw ValidationException::withMessages([
                                'supplier_name' => ['Supplier name is required when creating a new supplier.'],
                            ]);
                        }

                        $supplier = Supplyer::create([
                            'name' => $supplierName,
                            'phone' => $supplierPhone,
                        ]);
                    } elseif ($supplierName && $supplier->name !== $supplierName) {
                        $supplier->update(['name' => $supplierName]);
                    }
                }

                $subtotal = round($cartItems->sum(
                    fn($item) => (float) $item->price * (int) $item->quantity
                ), 2);

                $cartDiscount = round($cartItems->sum(
                    fn($item) => (float) ($item->discount ?? 0) * (int) $item->quantity
                ), 2);

                $manualDiscount = round(max(0, (float) ($validated['discount'] ?? 0)), 2);
                $discount = round(min($subtotal, $cartDiscount + $manualDiscount), 2);

                $vatPercentage = round(
                    min(100, max(0, (float) ($validated['vat'] ?? 0))),
                    2
                );

                $taxableAmount = round(max(0, $subtotal - $discount), 2);
                $vat = round(($taxableAmount * $vatPercentage) / 100, 2);
                $payableAmount = round(max(0, $taxableAmount + $vat), 2);

                $receivedAmount = round(
                    max(0, (float) ($validated['received_amount'] ?? 0)),
                    2
                );

                $paidAmount = round(min($receivedAmount, $payableAmount), 2);
                $dueAmount = round(max(0, $payableAmount - $paidAmount), 2);
                $changeAmount = round(max(0, $receivedAmount - $payableAmount), 2);

                if ($payableAmount > 0 && $paidAmount <= 0) {
                    throw ValidationException::withMessages([
                        'received_amount' => ['Payment amount must be greater than zero.'],
                    ]);
                }

                $isPartiallyPaid = $paidAmount > 0 && $paidAmount < $payableAmount;

                if ($isPartiallyPaid && !$supplier) {
                    throw ValidationException::withMessages([
                        'supplier_id' => ['Supplier is required for partial payment.'],
                    ]);
                }

                $paymentMethod = $validated['payment_method'] ?? PurchaseOrderPayment::METHOD_CASH;

                if (!in_array($paymentMethod, PurchaseOrderPayment::PAYMENT_METHODS, true)) {
                    throw ValidationException::withMessages([
                        'payment_method' => ['Invalid payment method.'],
                    ]);
                }

                $point = (int) $cartItems->sum(
                    fn($item) => (int) ($item->point ?? 0) * (int) $item->quantity
                );

                if ($payableAmount <= 0 || $paidAmount >= $payableAmount) {
                    $orderStatus = PurchaseOrder::STATUS_COMPLETED;
                } elseif ($paidAmount > 0) {
                    $orderStatus = PurchaseOrder::STATUS_PARTIALLY_PAID;
                } else {
                    $orderStatus = PurchaseOrder::STATUS_UNPAID;
                }

                $orderNumber = 'PO-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));
                $slug = Str::slug($orderNumber);

                $order = PurchaseOrder::create([
                    'reg' => $reg,
                    'order_number' => $orderNumber,
                    'slug' => $slug,
                    'order_date' => now()->toDateString(),
                    'user_id' => $user->id,
                    'supplier_id' => $supplier?->id,
                    'supplier_name' => $supplier?->name ?: $supplierName,
                    'supplier_phone' => $supplier?->phone ?: $supplierPhone,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'vat_percentage' => $vatPercentage,
                    'vat' => $vat,
                    'due_amount' => $dueAmount,
                    'payable_amount' => $payableAmount,
                    'payment_method' => $paymentMethod,
                    'currency' => PurchaseOrder::CURRENCY_BDT,
                    'point' => $point,
                    'status' => $orderStatus,
                    'completed_at' => $orderStatus === PurchaseOrder::STATUS_COMPLETED ? now() : null,
                    'remarks' => $validated['remarks'] ?? "Purchase order created by user: {$user->name}",
                    'paid_at' => $paidAmount > 0 ? now() : null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                $payment = null;

                if ($paidAmount > 0) {
                    $payment = PurchaseOrderPayment::create([
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'supplier_id' => $supplier?->id,
                        'payment_type' => PurchaseOrderPayment::TYPE_PAYMENT,
                        'payment_method' => $paymentMethod,
                        'amount' => $paidAmount,
                        'currency' => PurchaseOrderPayment::CURRENCY_BDT,
                        'paid_at' => now(),
                        'received_by' => $user->id,
                        'remarks' => $validated['remarks'] ?? "Purchase order payment received by user: {$user->name}",
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);
                }

                $order->load(['supplier', 'payments']);

                return [
                    'order' => $order,
                    'payment' => $payment,
                    'paid_amount' => $paidAmount,
                    'due_amount' => $dueAmount,
                    'change_amount' => $changeAmount,
                    'received_amount' => $receivedAmount,
                ];
            }, 3);

            return response()->json([
                'success' => true,
                'message' => 'Purchase order placed successfully.',
                'data' => [
                    'order' => $result['order'],
                    'payment' => $result['payment'],
                    'paid_amount' => $result['paid_amount'],
                    'due_amount' => $result['due_amount'],
                    'received_amount' => $result['received_amount'],
                    'change_amount' => $result['change_amount'],
                ],
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
            Log::error('Purchase order confirmation failed', [
                'user_id' => $user?->id,
                'reg' => $reg,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
