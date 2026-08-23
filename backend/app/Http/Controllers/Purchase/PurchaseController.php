<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

use App\Models\Product;
use App\Models\Stock;
use App\Models\PurchaseCart;
use App\Models\PurchaseOrder;
use App\Models\Supplyer;
use App\Models\PurchaseOrderPayment;
use App\Services\PurchaseRegGenerator;
use App\Http\Requests\CheckOutPurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseCartRequest;

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
            'product_id' => ['required', 'exists:products,id'],
            'quantity'   => ['nullable', 'integer', 'min:1'],
        ]);

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $requestedQty = (int) ($data['quantity'] ?? 1);

        try {
            return DB::transaction(function () use ($data, $user, $requestedQty) {

                // Lock product row
                $product = Product::query()
                    ->lockForUpdate()
                    ->find($data['product_id']);

                if (!$product) {
                    throw ValidationException::withMessages([
                        'product_id' => ['Product not found.'],
                    ]);
                }

                if (!$product->is_active) {
                    throw ValidationException::withMessages([
                        'product_id' => ['This product is currently inactive.'],
                    ]);
                }

                // Generate current purchase cart registration
                $reg = PurchaseRegGenerator::generateOrderReg($user->id);

                if (!$reg) {
                    throw ValidationException::withMessages([
                        'reg' => ['Failed to generate cart session.'],
                    ]);
                }

                $basePrice = round(max(0, (float) $product->purchase_price), 2);
                $salePrice = round(max(0, (float) $product->price), 2);

                // Find user's cart item and lock it
                $cartItem = PurchaseCart::query()
                    ->where('user_id', $user->id)
                    ->where('reg', $reg)
                    ->where('product_id', $product->id)
                    ->lockForUpdate()
                    ->first();

                $currentQty = (int) ($cartItem?->quantity ?? 0);
                $newQty = $currentQty + $requestedQty;

                $totalAmount = round($basePrice * $newQty, 2);

                if ($cartItem) {

                    $cartItem->update([
                        'quantity'     => $newQty,
                        'price'        => $basePrice,
                        'sale_price'   => $salePrice,
                        'total_amount' => $totalAmount,
                    ]);

                } else {

                    $cartItem = PurchaseCart::create([
                        'reg'          => $reg,
                        'user_id'      => $user->id,
                        'product_id'   => $product->id,
                        'quantity'     => $requestedQty,
                        'price'        => $basePrice,
                        'sale_price'   => $salePrice,
                        'total_amount' => round($basePrice * $requestedQty, 2),
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart successfully.',
                    'data' => [
                        'cart_id'    => $cartItem->id,
                        'product_id' => $product->id,
                        'quantity'   => (int) $cartItem->quantity,
                        'price'      => (float) $cartItem->price,
                        'total'      => (float) $cartItem->total_amount,
                    ],
                ], 201);
            });

        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {

            Log::error('Purchase Add To Cart Failed', [
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
            ], 500);
        }
    }

    public function adminAddToCartSearch(Request $request)
    {
        $data = $request->validate([
            'product'  => ['required', 'string', 'max:255'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $search = trim($data['product']);
        $requestedQty = (int) ($data['quantity'] ?? 1);

        try {
            return DB::transaction(function () use ($data, $user, $search, $requestedQty) {

                /*
                * Product search
                */
                $product = Product::query()
                    ->where(function ($query) use ($search) {

                        $query->where('sku', $search)
                            ->orWhere('slug', $search)
                            ->orWhere('sku', 'LIKE', "{$search}%")
                            ->orWhere('slug', 'LIKE', "{$search}%")
                            ->orWhere('name', 'LIKE', "%{$search}%");

                        if (ctype_digit($search)) {
                            $query->orWhere('id', (int) $search);
                        }
                    })
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    throw ValidationException::withMessages([
                        'product' => ['Product not found.'],
                    ]);
                }

                if (!$product->is_active) {
                    throw ValidationException::withMessages([
                        'product' => ['This product is currently inactive.'],
                    ]);
                }

                /*
                * Generate current purchase cart registration
                */
                $reg = PurchaseRegGenerator::generateOrderReg($user->id);

                if (!$reg) {
                    throw ValidationException::withMessages([
                        'reg' => ['Failed to generate cart session.'],
                    ]);
                }

                $basePrice = round(max(0, (float) $product->purchase_price), 2);
                $salePrice = round(max(0, (float) $product->price), 2);

                /*
                * Find user's cart item and lock it
                */
                $cartItem = PurchaseCart::query()
                    ->where('user_id', $user->id)
                    ->where('reg', $reg)
                    ->where('product_id', $product->id)
                    ->lockForUpdate()
                    ->first();

                $currentQty = (int) ($cartItem?->quantity ?? 0);
                $newQty = $currentQty + $requestedQty;

                $totalAmount = round($basePrice * $newQty, 2);

                /*
                * Update / create cart item
                */
                if ($cartItem) {

                    $cartItem->update([
                        'quantity'     => $newQty,
                        'price'        => $basePrice,
                        'sale_price'   => $salePrice,
                        'total_amount' => $totalAmount,
                    ]);

                } else {

                    $cartItem = PurchaseCart::create([
                        'reg'          => $reg,
                        'user_id'      => $user->id,
                        'product_id'   => $product->id,
                        'quantity'     => $requestedQty,
                        'price'        => $basePrice,
                        'sale_price'   => $salePrice,
                        'total_amount' => round($basePrice * $requestedQty, 2),
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart successfully.',
                    'data' => [
                        'cart_id'    => $cartItem->id,
                        'product_id' => $product->id,
                        'quantity'   => (int) $cartItem->quantity,
                        'price'      => (float) $cartItem->price,
                        'total'      => (float) $cartItem->total_amount,
                    ],
                ], 201);
            });

        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {

            Log::error('Purchase Add To Cart Search Failed', [
                'user_id'  => $user->id,
                'product'  => $data['product'] ?? null,
                'quantity' => $requestedQty,
                'message'  => $e->getMessage(),
                'file'     => $e->getFile(),
                'line'     => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to add product to cart. Please try again.',
            ], 500);
        }
    }

    public function updateQty(Request $request, string $reg, int $product_id)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        try {
            return DB::transaction(function () use ($data, $reg, $product_id, $user) {

                $cartItem = PurchaseCart::query()
                    ->where('user_id', $user->id)
                    ->where('reg', $reg)
                    ->where('product_id', $product_id)
                    ->lockForUpdate()
                    ->first();

                if (!$cartItem) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cart item not found.',
                    ], 404);
                }

                $newQty = (int) $data['quantity'];

                $price = round((float) $cartItem->price, 2);

                $totalAmount = round($price * $newQty, 2);

                $cartItem->update([
                    'quantity'     => $newQty,
                    'total_amount' => $totalAmount,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Quantity updated successfully.',
                    'data' => [
                        'cart_id'     => $cartItem->id,
                        'product_id'  => $cartItem->product_id,
                        'quantity'    => $newQty,
                        'price'       => (float) $price,
                        'total_amount'=> (float) $totalAmount,
                    ],
                ], 200);
            }, 3);

        } catch (\Throwable $e) {

            Log::error('Purchase cart quantity update failed', [
                'user_id'    => $user->id,
                'reg'        => $reg,
                'product_id' => $product_id,
                'quantity'   => $data['quantity'] ?? null,
                'message'    => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update cart quantity. Please try again.',
            ], 500);
        }
    }

    public function updateCartItem(UpdatePurchaseCartRequest $request, int $id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        try {
            $validated = $request->validated();

            $cartItem = DB::transaction(function () use ($validated, $id, $user) {

                $cartItem = PurchaseCart::query()
                    ->where('id', $id)
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if (!$cartItem) {
                    throw ValidationException::withMessages([
                        'cart' => ['Cart item not found.'],
                    ]);
                }

                $price = round(
                    max(0, (float) $validated['price']),
                    2
                );

                $salePrice = round(
                    max(0, (float) $validated['sale_price']),
                    2
                );

                $quantity = (int) $validated['quantity'];

                $totalAmount = round(
                    $price * $quantity,
                    2
                );

                $cartItem->update([
                    'price'        => $price,
                    'quantity'     => $quantity,
                    'sale_price'   => $salePrice,
                    'total_amount' => $totalAmount,
                ]);

                return $cartItem
                    ->fresh()
                    ->load('product');
            }, 3);

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully.',
                'data' => $cartItem,
            ], 200);

        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {

            Log::error('Purchase cart item update failed', [
                'user_id'     => $user->id,
                'cart_item_id'=> $id,
                'message'     => $e->getMessage(),
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update cart item. Please try again.',
            ], 500);
        }
    }

    public function removeToCart(int $cart_id, string $reg, int $product_id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        try {

            return DB::transaction(function () use (
                $cart_id,
                $reg,
                $product_id,
                $user
            ) {

                $cartItem = PurchaseCart::query()
                    ->where('id', $cart_id)
                    ->where('user_id', $user->id)
                    ->where('reg', $reg)
                    ->where('product_id', $product_id)
                    ->lockForUpdate()
                    ->first();

                if (!$cartItem) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cart item not found.',
                    ], 404);
                }

                $cartItem->delete();

                $remainingItems = PurchaseCart::query()
                    ->where('user_id', $user->id)
                    ->where('reg', $reg)
                    ->count();

                return response()->json([
                    'success' => true,
                    'message' => 'Item removed successfully.',
                    'remaining_items' => $remainingItems,
                ], 200);

            }, 3);

        } catch (\Throwable $e) {

            Log::error('Purchase cart remove failed', [
                'user_id'    => $user->id,
                'cart_id'    => $cart_id,
                'reg'        => $reg,
                'product_id' => $product_id,
                'message'    => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => app()->isProduction()
                    ? 'Unable to remove cart item.'
                    : $e->getMessage(),
            ], 500);
        }
    }

    public function getCartItem($reg)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.',
                ], 401);
            }

            $items = PurchaseCart::with(['product.images','user'])->where('user_id', $user->id)
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
                    ->where('user_id', $user->id)
                    ->where('reg', $reg)
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
                    fn ($item) => (float) $item->price * (int) $item->quantity
                ), 2);

                $manualDiscount = round(max(0, (float) ($validated['discount'] ?? 0)), 2);
                $discount = round(min($subtotal, $manualDiscount), 2);

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

                foreach ($cartItems as $item) {
                    $product = Product::query()
                        ->lockForUpdate()
                        ->find($item->product_id);

                    if (!$product) {
                        throw ValidationException::withMessages([
                            'product' => ["Product not found: {$item->product_id}"],
                        ]);
                    }

                    $quantity = (int) $item->quantity;

                    if ($quantity < 1) {
                        throw ValidationException::withMessages([
                            'quantity' => ["Invalid quantity for product {$product->id}."],
                        ]);
                    }

                    $product->increment('stock_quantity', $quantity);

                    Stock::create([
                        'product_id' => $product->id,
                        'batch_no' => $item->batch_no ?? null,
                        'reg' => $reg,
                        'date' => now()->toDateString(),
                        'purchase_price' => round((float) $item->price, 2),
                        'sale_price' => round((float) ($item->sale_price ?? $product->price), 2),
                        'stockIn' => $quantity,
                        'stockOut' => 0,
                        'expiry_date' => $item->expiry_date ?? null,
                        'remark' => "Purchase order: {$order->order_number}",
                        'status' => 'active',
                    ]);
                }

                PurchaseCart::query()
                    ->where('user_id', $user->id)
                    ->where('reg', $reg)
                    ->delete();

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
                'user_id' => $user->id,
                'reg' => $reg,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
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
