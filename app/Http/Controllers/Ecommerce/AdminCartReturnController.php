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
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

use App\Models\User;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Cart;
use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use App\Models\Customer;
use App\Models\Order;
use App\Services\RegGenerator;
use App\Http\Requests\CheckOutRequest;
use App\Http\Requests\OrderReturnRequest;
use App\Mail\OrderMail;
use App\Models\OrderPayment;
use App\Services\PointService;

class AdminCartReturnController extends Controller
{
    protected $pointService;

    public function __construct(PointService $pointService)
    {
        $this->pointService = $pointService;
    }

    public function index(string $reg, string $slug)
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized user',
            ], 401);
        }

        try {

            /*
            |--------------------------------------------------------------------------
            | Get Order
            |--------------------------------------------------------------------------
            */

            $order = Order::query()
                ->with([
                    'customer',
                    'user',
                ])
                ->where('reg', $reg)
                ->where('slug', $slug)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.',
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | Get Cart Items
            |--------------------------------------------------------------------------
            */

            $items = Cart::query()
                ->with([
                    'product.images',
                    'stock',
                    'user',
                ])
                ->where('reg', $order->reg)
                ->orderBy('id', 'asc')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Get Payments
            |--------------------------------------------------------------------------
            */

            $payments = OrderPayment::query()
                ->with([
                    'customer',
                    'user',
                    'receiver',
                    'verifier',
                ])
                ->where('order_id', $order->id)
                ->orderBy('paid_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Get Returns
            |--------------------------------------------------------------------------
            */

            $returns = OrderReturn::query()
                ->with([
                    'user',
                    'customer',
                    'approvedBy',
                    'items.product.images',
                    'items.stock',
                    'items.cart',
                ])
                ->where('order_id', $order->id)
                ->latest('id')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Cart Summary
            |--------------------------------------------------------------------------
            */

            $totalQuantity = (int) $items->sum(
                fn ($item) => (int) $item->quantity
            );

            $subtotal = round(
                $items->sum(
                    fn ($item) =>
                        (float) $item->price *
                        (int) $item->quantity
                ),
                2
            );

            $discount = round(
                $items->sum(
                    fn ($item) =>
                        (float) ($item->discount ?? 0) *
                        (int) $item->quantity
                ),
                2
            );

            $total = round(
                max(
                    0,
                    $subtotal - $discount
                ),
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Payment Summary
            |--------------------------------------------------------------------------
            */

            $totalPaid = round(
                $payments
                    ->where(
                        'payment_type',
                        OrderPayment::TYPE_PAYMENT
                    )
                    ->sum(
                        fn ($payment) =>
                            (float) $payment->amount
                    ),
                2
            );

            $totalRefunded = round(
                abs(
                    $payments
                        ->where(
                            'payment_type',
                            OrderPayment::TYPE_REFUND
                        )
                        ->sum(
                            fn ($payment) =>
                                (float) $payment->amount
                        )
                ),
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Return Summary
            |--------------------------------------------------------------------------
            */

            $returnedQuantity = (int) $returns
                ->flatMap(
                    fn ($return) => $return->items
                )
                ->sum(
                    fn ($item) => (int) $item->quantity
                );

            $returnSubtotal = round(
                $returns->sum(
                    fn ($return) =>
                        (float) $return->subtotal
                ), 2
            );

            $returnDiscount = round(
                $returns->sum(
                    fn ($return) =>
                        (float) $return->discount
                ),
                2
            );

            $returnVat = round(
                $returns->sum(
                    fn ($return) =>
                        (float) $return->vat
                ),
                2
            );

            $returnAmount = round(
                $returns->sum(
                    fn ($return) =>
                        (float) $return->refund_amount
                ),
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Final Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' => true,
                'message' => 'Order details fetched successfully.',
                'reg' => $order->reg,
                'data' => [
                    'order' => $order,
                    'cart_items' => $items,
                    'payments' => $payments,
                    'returns' => $returns,
                ],
                'summary' => [
                    'cart' => [
                        'total_items' => $items->count(),
                        'total_quantity' => $totalQuantity,
                        'subtotal' => $subtotal,
                        'discount' => $discount,
                        'total' => $total,
                    ],

                    'payment' => [
                        'total_paid' => $totalPaid,
                        'total_refunded' => $totalRefunded,
                        'net_received' => round(
                            $totalPaid - $totalRefunded, 2
                        ),
                    ],

                    'return' => [
                        'total_returns' => $returns->count(),
                        'returned_quantity' => $returnedQuantity,
                        'subtotal' => $returnSubtotal,
                        'discount' => $returnDiscount,
                        'vat' => $returnVat,
                        'refund_amount' => $returnAmount,
                    ],
                ],
            ], 200);

        } catch (\Throwable $e) {

            Log::error('POS Order Details Fetch Failed', [
                'user_id' => $userId,
                'reg' => $reg,
                'slug' => $slug,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch order details. Please try again.',
            ], 500);
        }
    }

    public function adminAddToCart(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        try {

            return $this->addProductToCartFIFO(
                (int) $data['product_id'],
                (int) ($data['quantity'] ?? 1),
                $user
            );

        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {

            Log::error('POS Add To Cart Failed', [
                'user_id' => $user->id,
                'product_id' => $data['product_id'] ?? null,
                'quantity' => $data['quantity'] ?? 1,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
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
            'product' => [
                'required',
                'string',
                'max:255',
            ],
            'quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ]);

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $search = trim($data['product']);

        if ($search === '') {
            return response()->json([
                'success' => false,
                'message' => 'Product search value is required.',
            ], 422);
        }

        try {

            $product = Product::query()
                ->where(function ($query) use ($search) {

                    if (ctype_digit($search)) {
                        $query->where('id', (int) $search)
                            ->orWhere('sku', $search)
                            ->orWhere('slug', $search);
                    } else {
                        $query->where('sku', $search)
                            ->orWhere('slug', $search);
                    }

                    $query->orWhere(
                        'name',
                        'LIKE',
                        '%' . addcslashes($search, '%_') . '%'
                    );
                })
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.',
                ], 404);
            }

            if (!$product->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'This product is currently inactive.',
                ], 422);
            }

            // Reuse the same FIFO cart logic
            $quantity = (int) ($data['quantity'] ?? 1);

            return $this->addProductToCartFIFO(
                $product->id,
                $quantity,
                $user
            );

        } catch (\Throwable $e) {

            Log::error('POS Add To Cart Search Failed', [

                'user_id' => $user->id,

                'search' => $search,

                'quantity' => $data['quantity'] ?? 1,

                'message' => $e->getMessage(),

                'file' => $e->getFile(),

                'line' => $e->getLine(),

            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to add product to cart. Please try again.',
            ], 500);
        }
    }

    private function addProductToCartFIFO(
        int $productId,
        int $requestedQty,
        $user
    ) {
        return DB::transaction(function () use (
            $productId,
            $requestedQty,
            $user
        ) {

            // =====================================================
            // PRODUCT
            // =====================================================

            $product = Product::query()
                ->whereKey($productId)
                ->lockForUpdate()
                ->first();

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


            // =====================================================
            // CART REG
            // =====================================================

            $reg = RegGenerator::generateOrderReg($user->id);

            if (!$reg) {
                throw ValidationException::withMessages([
                    'reg' => 'Failed to generate cart session.',
                ]);
            }


            // =====================================================
            // EXISTING PRODUCT QTY IN CART
            // =====================================================

            $existingQty = (int) Cart::query()
                ->where('reg', $reg)
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->sum('quantity');


            $newTotalQty = $existingQty + $requestedQty;


            // =====================================================
            // LOCK STOCKS
            // =====================================================

            $stocks = Stock::query()
                ->where('product_id', $product->id)
                ->where('status', 'active')
                ->where(function ($query) {
                    $query
                        ->whereNull('expiry_date')
                        ->orWhereDate('expiry_date', '>=', today());
                })
                ->whereRaw('stockIn > stockOut')
                ->orderBy('date', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();


            if ($stocks->isEmpty()) {
                throw ValidationException::withMessages([
                    'product_id' => 'This product is out of stock.',
                ]);
            }


            // =====================================================
            // TOTAL AVAILABLE
            // =====================================================

            $totalAvailableStock = $stocks->sum(
                fn ($stock) => max(
                    0,
                    (int) $stock->stockIn
                    - (int) $stock->stockOut
                )
            );


            if ($newTotalQty > $totalAvailableStock) {

                throw ValidationException::withMessages([
                    'quantity' => [
                        "Only {$totalAvailableStock} item(s) available in stock."
                    ],
                ]);
            }


            // =====================================================
            // REBUILD PRODUCT CART FIFO
            // =====================================================

            Cart::query()
                ->where('reg', $reg)
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->delete();


            $remainingQty = $newTotalQty;

            $cartItems = [];


            foreach ($stocks as $stock) {

                if ($remainingQty <= 0) {
                    break;
                }


                $availableQty = max(
                    0,
                    (int) $stock->stockIn
                    - (int) $stock->stockOut
                );


                if ($availableQty <= 0) {
                    continue;
                }


                $allocateQty = min(
                    $remainingQty,
                    $availableQty
                );


                $salePrice = round(
                    (float) $stock->sale_price,
                    2
                );


                if ($salePrice < 0) {
                    throw ValidationException::withMessages([
                        'product_id' => 'Invalid stock sale price.',
                    ]);
                }


                $discountAmount = round(
                    max(
                        0,
                        (float) ($product->discount ?? 0)
                    ),
                    2
                );


                $finalPrice = round(
                    max(
                        0,
                        $salePrice - $discountAmount
                    ),
                    2
                );


                $cartItem = Cart::create([

                    'reg' => $reg,

                    'user_id' => $user->id,

                    'product_id' => $product->id,

                    'stock_id' => $stock->id,

                    'quantity' => $allocateQty,

                    'price' => $salePrice,

                    'discount' => $discountAmount,

                    'total_amount' => round(
                        $finalPrice * $allocateQty,
                        2
                    ),

                    'point' => $product->point,
                ]);


                $cartItems[] = $cartItem;

                $remainingQty -= $allocateQty;
            }


            if ($remainingQty > 0) {
                throw ValidationException::withMessages([
                    'quantity' => [
                        'Unable to allocate quantity using FIFO stock.'
                    ],
                ]);
            }


            return response()->json([

                'success' => true,

                'message' => 'Product added to cart successfully.',

                'data' => [

                    'reg' => $reg,

                    'product_id' => $product->id,

                    'product_name' => $product->name,

                    'quantity' => $newTotalQty,

                    'available_stock' => $totalAvailableStock,

                    'remaining_stock' => max(
                        0,
                        $totalAvailableStock - $newTotalQty
                    ),

                    'items' => collect($cartItems)
                        ->map(function ($item) {

                            return [
                                'cart_id' => $item->id,
                                'product_id' => $item->product_id,
                                'stock_id' => $item->stock_id,
                                'quantity' => (int) $item->quantity,
                                'price' => (float) $item->price,
                                'discount' => (float) $item->discount,
                                'total' => (float) $item->total_amount,
                            ];

                        })
                        ->values(),
                ],

            ], 201);
        });
    }

    public function updateQty(Request $request, $reg, $product_id)
    {
        $data = $request->validate([
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:10000',
            ],
        ]);

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        try {

            return $this->rebuildCartProductFIFO(
                $reg,
                (int) $product_id,
                (int) $data['quantity'],
                $user
            );

        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {

            Log::error('Cart Qty Update Error', [
                'user_id' => $user->id,
                'reg' => $reg,
                'product_id' => $product_id,
                'quantity' => $data['quantity'],
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update cart quantity.',
            ], 500);
        }
    }

    private function rebuildCartProductFIFO(
        string $reg,
        int $productId,
        int $quantity,
        $user
    ) {
        return DB::transaction(function () use (
            $reg,
            $productId,
            $quantity,
            $user
        ) {

            // =====================================================
            // PRODUCT
            // =====================================================

            $product = Product::query()
                ->whereKey($productId)
                ->lockForUpdate()
                ->first();

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


            // =====================================================
            // LOCK STOCKS
            // =====================================================

            $stocks = Stock::query()
                ->where('product_id', $productId)
                ->where('status', 'active')
                ->where(function ($query) {
                    $query
                        ->whereNull('expiry_date')
                        ->orWhereDate('expiry_date', '>=', today());
                })
                ->whereRaw('stockIn > stockOut')
                ->orderBy('date', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();


            $totalAvailableStock = $stocks->sum(
                fn ($stock) => max(
                    0,
                    (int) $stock->stockIn
                    - (int) $stock->stockOut
                )
            );


            if ($quantity > $totalAvailableStock) {
                throw ValidationException::withMessages([
                    'quantity' => [
                        "Only {$totalAvailableStock} item(s) available in stock."
                    ],
                ]);
            }


            // =====================================================
            // DELETE CURRENT ALLOCATION
            // =====================================================

            Cart::query()
                ->where('reg', $reg)
                ->where('user_id', $user->id)
                ->where('product_id', $productId)
                ->delete();


            // =====================================================
            // FIFO REBUILD
            // =====================================================

            $remainingQty = $quantity;

            $items = [];


            foreach ($stocks as $stock) {

                if ($remainingQty <= 0) {
                    break;
                }


                $availableQty = max(
                    0,
                    (int) $stock->stockIn
                    - (int) $stock->stockOut
                );


                if ($availableQty <= 0) {
                    continue;
                }


                $allocateQty = min(
                    $remainingQty,
                    $availableQty
                );


                $salePrice = round(
                    (float) $stock->sale_price,
                    2
                );


                $discountAmount = round(
                    max(
                        0,
                        (float) ($product->discount ?? 0)
                    ),
                    2
                );


                $finalPrice = round(
                    max(
                        0,
                        $salePrice - $discountAmount
                    ),
                    2
                );


                $items[] = Cart::create([

                    'reg' => $reg,

                    'user_id' => $user->id,

                    'product_id' => $productId,

                    'stock_id' => $stock->id,

                    'quantity' => $allocateQty,

                    'price' => $salePrice,

                    'discount' => $discountAmount,

                    'total_amount' => round(
                        $finalPrice * $allocateQty,
                        2
                    ),

                    'point' => $product->point,
                ]);


                $remainingQty -= $allocateQty;
            }


            if ($remainingQty > 0) {
                throw ValidationException::withMessages([
                    'quantity' => [
                        'Unable to allocate FIFO stock.',
                    ],
                ]);
            }


            return response()->json([
                'success' => true,
                'message' => 'Cart quantity updated successfully.',
                'data' => [
                    'reg' => $reg,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'items' => collect($items)->map(function ($item) {
                        return [
                            'cart_id' => $item->id,
                            'stock_id' => $item->stock_id,
                            'quantity' => (int) $item->quantity,
                            'price' => (float) $item->price,
                            'discount' => (float) $item->discount,
                            'total' => (float) $item->total_amount,
                        ];
                    })->values(),
                ],
            ], 200);
        });
    }

    public function removeToCart(
        Request $request,
        $cart_id,
        $reg,
        $product_id
    ) {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        try {

            return DB::transaction(function () use (
                $user,
                $cart_id,
                $reg,
                $product_id
            ) {

                $cartItem = Cart::query()
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


                // =====================================================
                // Delete ONLY this cart allocation
                // =====================================================

                $cartItem->delete();


                // =====================================================
                // Remaining cart items
                // =====================================================

                $remaining = Cart::query()
                    ->where('user_id', $user->id)
                    ->where('reg', $reg)
                    ->count();


                return response()->json([
                    'success' => true,
                    'message' => 'Cart item removed successfully.',
                    'remaining_items' => $remaining,
                ], 200);
            });

        } catch (\Throwable $e) {

            Log::error('Cart Remove Error', [
                'user_id' => $user->id,
                'cart_id' => $cart_id,
                'reg' => $reg,
                'product_id' => $product_id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to remove cart item.',
            ], 500);
        }
    }

    public function getCartItem($reg)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        try {

            $items = Cart::query()
                ->with([
                    'product.images',
                    'user',
                    'stock:id,product_id,batch_no,reg,sale_price,purchase_price,stockIn,stockOut,expiry_date,status',
                ])
                ->where('reg', $reg)
                ->where('user_id', $user->id)
                ->orderBy('id')
                ->get();


            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No cart items found.',
                    'data' => [],
                ], 404);
            }


            return response()->json([
                'success' => true,
                'message' => 'Cart items fetched successfully.',
                'reg' => $reg,
                'data' => $items,
            ], 200);

        } catch (\Throwable $e) {

            Log::error('Cart fetch error', [
                'user_id' => $user->id,
                'reg' => $reg,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching cart items.',
            ], 500);
        }
    }

    public function checkOutReturn(OrderReturnRequest $request, string $reg)
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

                /*
                |--------------------------------------------------------------------------
                | Lock Order
                |--------------------------------------------------------------------------
                */
                $order = Order::query()
                    ->where('reg', $reg)
                    ->lockForUpdate()
                    ->first();

                if (!$order) {
                    throw ValidationException::withMessages([
                        'reg' => ['Order not found.'],
                    ]);
                }

                if (!in_array($order->status, [
                    Order::STATUS_COMPLETED,
                    Order::STATUS_PARTIALLY_PAID,
                    Order::STATUS_PARTIALLY_RETURNED,
                ], true)) {
                    throw ValidationException::withMessages([
                        'order' => ['This order is not eligible for return.'],
                    ]);
                }

                $subtotal = abs((float) $order->subtotal);
                $orderDiscount = abs((float) $order->discount);
                $vatPercentage = abs((float) $order->vat_percentage);

                // Ratio at which original discount reduced the subtotal.
                // Used to prorate discount to each returned line so partial
                // returns stay consistent with the original order totals.
                $discountRatio = $subtotal > 0
                    ? min(1, $orderDiscount / $subtotal)
                    : 0;

                /*
                |--------------------------------------------------------------------------
                | Customer
                |--------------------------------------------------------------------------
                */
                $customer = $order->customer_id
                    ? Customer::query()
                        ->whereKey($order->customer_id)
                        ->lockForUpdate()
                        ->first()
                    : null;

                /*
                |--------------------------------------------------------------------------
                | Validate & Lock Requested Items
                |--------------------------------------------------------------------------
                */
                $requestedItems = collect($validated['items']);
                $cartIds = $requestedItems->pluck('cart_id')->all();

                $cartItems = Cart::query()
                    ->where('reg', $reg)
                    ->whereIn('id', $cartIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($cartItems->count() !== count($cartIds)) {
                    throw ValidationException::withMessages([
                        'items' => ['One or more selected items do not belong to this order.'],
                    ]);
                }

                $lineSubtotalTotal = 0.0;
                $lineDiscountTotal = 0.0;
                $lineVatTotal = 0.0;
                $lineRefundTotal = 0.0;
                $lineDetails = [];

                foreach ($requestedItems as $requestedItem) {

                    $cartItem = $cartItems->get($requestedItem['cart_id']);
                    $returnQty = (int) $requestedItem['quantity'];

                    $alreadyReturned = (int) $cartItem->returned_quantity;
                    $availableToReturn = (int) $cartItem->quantity - $alreadyReturned;

                    if ($returnQty > $availableToReturn) {
                        throw ValidationException::withMessages([
                            'items' => [
                                "Cannot return {$returnQty} unit(s) for product ID {$cartItem->product_id}. " .
                                "Only {$availableToReturn} unit(s) eligible for return."
                            ],
                        ]);
                    }

                    $unitPrice = (float) $cartItem->price;
                    $unitDiscount = (float) ($cartItem->discount ?? 0);

                    $lineGrossSubtotal = round($unitPrice * $returnQty, 2);

                    $lineDiscount = round(
                        min($lineGrossSubtotal, $lineGrossSubtotal * $discountRatio),
                        2
                    );

                    $lineDiscountedSubtotal = round(
                        max(0, $lineGrossSubtotal - $lineDiscount),
                        2
                    );

                    $lineVat = round(
                        ($lineDiscountedSubtotal * $vatPercentage) / 100,
                        2
                    );

                    $lineRefundAmount = round($lineDiscountedSubtotal + $lineVat, 2);

                    $lineSubtotalTotal += $lineGrossSubtotal;
                    $lineDiscountTotal += $lineDiscount;
                    $lineVatTotal += $lineVat;
                    $lineRefundTotal += $lineRefundAmount;

                    $lineDetails[] = [
                        'cart_item' => $cartItem,
                        'return_qty' => $returnQty,
                        'unit_price' => $unitPrice,
                        'unit_discount' => $unitDiscount,
                        'line_subtotal' => $lineGrossSubtotal,
                        'line_discount' => $lineDiscount,
                        'line_vat' => $lineVat,
                        'line_refund_amount' => $lineRefundAmount,
                    ];
                }

                $lineSubtotalTotal = round($lineSubtotalTotal, 2);
                $lineDiscountTotal = round($lineDiscountTotal, 2);
                $lineVatTotal = round($lineVatTotal, 2);
                $lineRefundTotal = round($lineRefundTotal, 2);

                if ($lineRefundTotal <= 0) {
                    throw ValidationException::withMessages([
                        'items' => ['Return amount must be greater than zero.'],
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Guard: don't refund more than what's left on the order
                |--------------------------------------------------------------------------
                */
                $payableAmount = abs((float) $order->payable_amount);
                $alreadyRefunded = abs((float) $order->refunded_amount);
                $refundableRemaining = round(max(0, $payableAmount - $alreadyRefunded), 2);

                if ($lineRefundTotal > $refundableRemaining) {
                    throw ValidationException::withMessages([
                        'items' => [
                            "Refund amount ({$lineRefundTotal}) exceeds remaining refundable amount ({$refundableRemaining})."
                        ],
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Create Order Return (header)
                |--------------------------------------------------------------------------
                */
                $orderReturn = OrderReturn::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'customer_id' => $customer?->id,
                    'reg' => $reg,

                    'subtotal' => $lineSubtotalTotal,
                    'discount' => $lineDiscountTotal,
                    'vat_percentage' => $vatPercentage,
                    'vat' => $lineVatTotal,
                    'refund_amount' => $lineRefundTotal,

                    'refund_method' => $order->payment_method,
                    'remarks' => $validated['remarks'] ?? "Return processed by user: {$user->name}",

                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Per-line: record return, restock
                |--------------------------------------------------------------------------
                */
                foreach ($lineDetails as $line) {

                    /** @var Cart $cartItem */
                    $cartItem = $line['cart_item'];

                    OrderReturnItem::create([
                        'order_return_id' => $orderReturn->id,
                        'cart_id' => $cartItem->id,
                        'product_id' => $cartItem->product_id,
                        'stock_id' => $cartItem->stock_id,

                        'quantity' => $line['return_qty'],
                        'unit_price' => $line['unit_price'],
                        'unit_discount' => $line['unit_discount'],

                        'subtotal' => $line['line_subtotal'],
                        'discount' => $line['line_discount'],
                        'vat' => $line['line_vat'],
                        'refund_amount' => $line['line_refund_amount'],
                    ]);

                    $cartItem->increment('returned_quantity', $line['return_qty']);

                    // Restock: reverse exactly what checkout did (batch-accurate).
                    if ($cartItem->stock_id) {
                        $stock = Stock::query()
                            ->whereKey($cartItem->stock_id)
                            ->where('product_id', $cartItem->product_id)
                            ->lockForUpdate()
                            ->first();

                        if ($stock) {
                            $stock->decrement('stockOut', $line['return_qty']);
                        }
                    }

                    $product = Product::query()
                        ->whereKey($cartItem->product_id)
                        ->lockForUpdate()
                        ->first();

                    if ($product) {
                        $product->increment('stock_quantity', $line['return_qty']);
                    }

                    Cache::forget('public_products');
                }

                /*
                |--------------------------------------------------------------------------
                | Refund Payment Record
                |--------------------------------------------------------------------------
                | Stored as a POSITIVE amount because sale payments were stored
                | negative (-$paidAmount) in checkOutReturn — this line reverses
                | that ledger entry, keeping the sign convention consistent.
                */
                $refundPayment = OrderPayment::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'customer_id' => $customer?->id,
                    'received_by' => $user->id,

                    'payment_type' => OrderPayment::TYPE_REFUND,
                    'payment_method' => $order->payment_method,

                    'amount' => $lineRefundTotal,
                    'currency' => OrderPayment::CURRENCY_BDT,

                    'paid_at' => now(),
                    'remarks' => $validated['remarks'] ?? 'Order return refund',

                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Update Order totals / status
                |--------------------------------------------------------------------------
                */
                $newRefundedTotal = round($alreadyRefunded + $lineRefundTotal, 2);

                $isFullyReturned = $newRefundedTotal >= $payableAmount
                    && $order->cartItems()
                        ->whereColumn('returned_quantity', '<', 'quantity')
                        ->doesntExist();

                $order->update([
                    'refunded_amount' => $newRefundedTotal,
                    'status' => $isFullyReturned
                        ? Order::STATUS_RETURNED
                        : Order::STATUS_PARTIALLY_RETURNED,
                    'returned_at' => now(),
                    'returned_by' => $user->id,
                ]);

                $order->load(['customer', 'payments']);

                return [
                    'order' => $order,
                    'order_return' => $orderReturn->load('items'),
                    'refund_payment' => $refundPayment,
                    'refund_amount' => $lineRefundTotal,
                ];

            }, 3);

            return response()->json([
                'success' => true,
                'message' => 'Return processed successfully.',
                'data' => $result,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
            Log::error('Order return failed', [
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
