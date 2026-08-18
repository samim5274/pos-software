<?php

namespace App\Http\Controllers\Expenses;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use Session;
use App\Models\Expense;
use App\Models\ExCategory;
use App\Models\ExSubCategory;


class ExpensesController extends Controller
{
    public function index()
    {
        try {
            $today = now()->toDateString();

            $expenses = Expense::query()
                ->with([
                    'category:id,name',
                    'subcategory:id,name',
                    'user:id,name',
                ])
                ->whereDate('date', $today)
                ->orderByDesc('id')
                ->paginate(20);

            $categories = ExCategory::query()
                ->orderByDesc('id')
                ->get();

            $subcategories = ExSubCategory::query()
                ->orderByDesc('id')
                ->get();

            return response()->json([
                'success' => true,
                'message' => "Today's expenses fetched successfully.",
                'data' => [
                    'expenses' => $expenses,
                    'categories' => $categories,
                    'subcategories' => $subcategories,
                ],
            ], 200);

        } catch (\Throwable $e) {

            Log::error('Failed to fetch today expenses.', [
                'user_id' => auth()->id(),
                'date'    => now()->toDateString(),
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch today\'s expenses.',
            ], 500);
        }
    }

    public function getExCategory()
    {
        try {
            $categories = ExCategory::query()
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Expense categories fetched successfully.',
                'data'    => $categories,
            ], 200);

        } catch (\Throwable $e) {

            Log::error('Failed to fetch expense categories.', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch expense categories.',
            ], 500);
        }
    }

    public function getExSubCategory()
    {
        try {
            $subcategories = ExSubCategory::query()
                ->where('category_id', $id)
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Expense subcategories fetched successfully.',
                'data'    => $subcategories,
            ], 200);

        } catch (\Throwable $e) {

            Log::error('Failed to fetch expense subcategories.', [
                'category_id' => $id,
                'error'       => $e->getMessage(),
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch expense subcategories.',
            ], 500);
        }
    }
}
