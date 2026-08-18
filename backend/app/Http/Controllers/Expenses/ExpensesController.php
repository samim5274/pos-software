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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'     => ['required', 'integer', 'exists:ex_categories,id'],
            'sub_category_id' => ['nullable', 'integer', 'exists:ex_sub_categories,id'],
            'title'           => ['required', 'string', 'max:255'],
            'amount'          => ['required', 'numeric', 'min:0.01'],
            'remark'          => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $expense = Expense::create([
                'category_id'     => $validated['category_id'],
                'sub_category_id' => $validated['sub_category_id'] ?? null,
                'user_id'         => auth()->id(),
                'title'           => $validated['title'],
                'date'            => now()->toDateString(),
                'amount'          => $validated['amount'],
                'remark'          => $validated['remark'] ?? "N/A",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expense created successfully.',
                'data'    => $expense,
            ], 201);

        } catch (\Throwable $e) {

            Log::error('Failed to create expense.', [
                'user_id' => auth()->id(),
                'request' => $request->except(['password', 'token']),
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create expense at this time. Please try again later.',
            ], 500);
        }
    }

    public function printExpenses($id){
        try{
            $userId = auth()->id();
            $expense = Expense::with(['category','subcategory','user'])->where('user_id', $userId)->findOrFail($id);
            if(!$expense){
                return response()->json([
                    'success' => false,
                    'message' => 'Expense not found. Please try again.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $expense,
            ], 200);

        } catch (\Throwable $e) {
            \Log::error("Expense print error: ".$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
