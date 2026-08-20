<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\Product;
use App\Models\Stock;

class StockController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'stock' => ['required', 'numeric', 'min:1'],
        ]);

        try {
            return DB::transaction(function () use ($request, $id) {

                $product = Product::where('id', $id)
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found.',
                    ], 404);
                }

                $stockQty = (float) $request->stock;

                do {
                    $reg = 'STK-' . strtoupper(Str::random(10));
                } while (Stock::where('reg', $reg)->exists());

                $product->increment('stock_quantity', $stockQty);

                $stock = Stock::create([
                    'reg'       => $reg,
                    'date'      => now()->toDateString(),
                    'product_id'=> $product->id,
                    'stockIn'   => $stockQty,
                    'stockOut'  => 0,
                    'remark'    => 'Stock added by: '. auth()->user()->name,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Product stock added successfully.',
                    'data' => [
                        'product' => $product->fresh(),
                        'stock'   => $stock,
                    ],
                ], 201);
            });

        } catch (\Throwable $e) {

            Log::error('Failed to add product stock.', [
                'product_id' => $id,
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add product stock.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
