<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ========================================
// Controllers
// ========================================
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ProfileController;

use App\Http\Controllers\Product\ProductController;

use App\Http\Controllers\Customer\CustomerController;

use App\Http\Controllers\Dashboard\DashboardController;

use App\Http\Controllers\Ecommerce\EcommerceProductController;
use App\Http\Controllers\Ecommerce\SearchController;
use App\Http\Controllers\Ecommerce\CartController;
use App\Http\Controllers\Ecommerce\AdminCartController;
use App\Http\Controllers\Ecommerce\RatingController;
use App\Http\Controllers\Ecommerce\SliderController;

use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\CouponController;

use App\Http\Controllers\Notice\NoticeController;

use App\Http\Controllers\Expense\ExpenseController;

use App\Http\Controllers\Admin\AdminController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ======================
// Auth Routes
// ======================
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/find-account', [AuthController::class, 'findAccount']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::post('/logout-device', [AuthController::class, 'logoutDevice']);
        Route::get('/devices', [AuthController::class, 'devices']);
    });
});

Route::prefix('register')->group(function () {
    // Route::get('/get-refer/{referCode}', [AuthController::class, 'getReferUser']);
    // Route::get('/products', [AuthController::class, 'getProducts']);
    // Route::get('/root-users', [AuthController::class, 'getUsers']);
    Route::post('/create-user', [AuthController::class, 'register']);
});




















// ======================
// Profile Routes
// ======================
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('profile')->group(function () {
        Route::put('/customer', [ProfileController::class, 'update']);
        Route::patch('/password', [ProfileController::class, 'changePassword']);
    });
});
















// ======================
// Product Routes
// ======================
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('products')->group(function () {
        Route::post('/create', [ProductController::class, 'store']);
        Route::get('/', [ProductController::class, 'index']);

        Route::get('/get-categories', [ProductController::class, 'getCategory']);
        Route::get('/get-subcategories', [ProductController::class, 'getSubCategory']);
        Route::get('/get-brands', [ProductController::class, 'getBrand']);

        Route::post('/create-brand', [ProductController::class, 'storeBrand']);
        Route::delete('/delete-brand/{id}', [ProductController::class, 'deleteBrand']);
        Route::put('/edit-brand/{id}', [ProductController::class, 'editBrand']);

        Route::post('/create-category', [ProductController::class, 'storeCategory']);
        Route::delete('/delete-category/{id}', [ProductController::class, 'deleteCategory']);
        Route::put('/edit-category/{id}', [ProductController::class, 'editCategory']);

        Route::post('/create-sub-category', [ProductController::class, 'storeSubCategory']);
        Route::delete('/delete-sub-category/{id}', [ProductController::class, 'deleteSubCategory']);
        Route::put('/edit-sub-category/{id}', [ProductController::class, 'editSubCategory']);

        // Product sale report Route
        Route::get('/report', [ProductController::class, 'reportSale']);

        // LAST: dynamic route for product details, must be at the end of all product routes
        Route::put('/update/{id}', [ProductController::class, 'edit'])->where('id', '[0-9]+');
        Route::delete('/delete/{id}', [ProductController::class, 'delete'])->where('id', '[0-9]+');
        Route::get('/{slug}', [ProductController::class, 'show'])->where('slug', '[a-zA-Z0-9\-]+');
    });
});


















// ======================
// Customer Routes
// ======================
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('customer')->group(function (){

        Route::prefix('addresses')->group(function(){
            Route::get('/get', [CustomerController::class, 'getAddress']);
            Route::post('/create', [CustomerController::class, 'createAddress']);
            Route::delete('/delete/{id}', [CustomerController::class, 'deleteAddress']);
        });

        Route::prefix('profile')->group(function () {
            Route::put('/', [CustomerController::class, 'update']);
            Route::put('/password', [CustomerController::class, 'changePassword']);
        });

        Route::prefix('orders')->group(function () {
            Route::get('/', [CustomerController::class, 'getOrders']);
            Route::post('/store', [CustomerController::class, 'storeOrder']);
        });

    });

    Route::prefix('dashboard')->group(function() {
        Route::get('/', [DashboardController::class, 'dashboard']);
    });
});

















// ======================
// dashboard Routes
// ======================
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('dashboard')->group(function() {
        Route::get('/', [DashboardController::class, 'dashboard']);
    });
});










// ======================
// E-commerce Routes
// ======================
// Route::prefix('public')->group(function () {

//     // Products
//     Route::get('/products', [EcommerceProductController::class, 'index']);
//     Route::get('/product/{slug}', [ProductController::class, 'show']);

//     // Categories
//     Route::get('/get-categories', [ProductController::class, 'getCategory']);
//     Route::get('/get-subcategories', [ProductController::class, 'getSubCategory']);
//     Route::get('/get-brands', [ProductController::class, 'getBrand']);

//     // Category Products
//     Route::get('/category-products/{id}', [EcommerceProductController::class, 'getCategoryProducts']);
//     Route::get('/products/category/group', [EcommerceProductController::class, 'categoryGroup']);

//     // Location
//     Route::get('/get-division', [EcommerceProductController::class, 'getDivision']);
//     Route::get('/get-district', [EcommerceProductController::class, 'getDistrict']);
//     Route::get('/get-upazila', [EcommerceProductController::class, 'getUpazila']);
//     Route::get('/get-police-station', [EcommerceProductController::class, 'getPoliceStation']);

//     // Other
//     Route::get('/{user_id}/details', [EcommerceProductController::class, 'otherDetails']);
// });

// =============================
// E-commerce Search
// =============================
// Route::prefix('search')->group(function () {
//     Route::get('/', [SearchController::class, 'search']);
//     Route::get('/suggestions', [SearchController::class, 'suggestions']);
// });

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('admin/cart')->group(function () {
        Route::get('/', [AdminCartController::class, 'index']);
        Route::get('/{reg}', [AdminCartController::class, 'getCartItem']);
        Route::post('/add-to-cart', [AdminCartController::class, 'adminAddToCart']);
        Route::post('/add-to-cart-search', [AdminCartController::class, 'adminAddToCartSearch']);
        Route::post('/qty-update/{reg}/{product_id}', [AdminCartController::class, 'updateQty']);
        Route::post('/remove-to-cart/{cart_id}/{reg}/{product_id}', [AdminCartController::class, 'removeToCart']);
        Route::post('/checkout/{reg}', [AdminCartController::class, 'checkOut']);
    });

    // Route::prefix('cart')->group(function () {
    //     Route::get('/', [CartController::class, 'index']);
    //     Route::get('/{reg}', [CartController::class, 'getCartItem']);
    //     Route::post('/add-to-cart', [CartController::class, 'addToCart']);
    //     Route::post('/qty-update/{reg}/{product_id}/{variant_id}', [CartController::class, 'updateQty']);
    //     Route::post('/remove-to-cart/{cart_id}/{reg}/{product_id}/{variant_id}', [CartController::class, 'removeToCart']);
    // });
});

// =============================
// Product Ratting
// =============================

// Route::get('/product/ratings/{product_id}', [RatingController::class, 'getProductRating']);

// Route::middleware('auth:sanctum')->group(function () {
//     Route::prefix('ratings')->group(function () {
//         Route::get('/', [RatingController::class, 'index']);
//         Route::post('/', [RatingController::class, 'store']);
//     });
// });








// =============================
// E-commerce Admin order Routes
// =============================
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/status', [OrderController::class, 'statusFilter']);

        // Route::get('/customer/{user_id}', [OrderController::class, 'getCustomerDetails']);
        // Route::get('/items/{reg}/payment/details', [EcommerceProductController::class, 'orderItemsDetails']);


        // Route::post('/confirm/{reg}', [OrderController::class, 'confirmOrder']);
        // Route::post('/payments/{payment_id}/verify', [OrderController::class, 'verifyPayment']);
        // Route::post('/{reg}/payments', [OrderController::class, 'confirmPayment']);
        // Route::post('/update-status/{reg}', [OrderController::class, 'updateStatus']);

        // Route::patch('/delivery-charge-payments/{id}/status', [OrderController::class, 'deliveryStatusUpdate']);

        // Route::prefix('reports')->group(function(){
        //     Route::get('/sale', [OrderController::class, 'reportSale']);
        //     Route::get('/sale/filter', [OrderController::class, 'reportSaleFilter']);
        // });

        // Route::get('/user/details', [EcommerceProductController::class, 'userOrderDetails']);

        // Route::post('/check-coupon', [CouponController::class, 'checkCoupon']);

        Route::get('/{reg}', [OrderController::class, 'getOrderDetails']);
    });
});

// =============================
// Coupon Controller
// =============================
// Route::middleware('auth:sanctum')->group(function () {
//     Route::prefix('coupon')->group(function () {
//         Route::post('/check', [CouponController::class, 'checkCoupon']);
//     });
// });






// ======================
// Notice Routes
// ======================
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('notice')->group(function () {
        Route::get('/', [NoticeController::class, 'index']);
        Route::get('/user', [NoticeController::class, 'userNotice']);
        Route::post('/create', [NoticeController::class, 'create']);
        // Route::get('/view/{file}', [NoticeController::class, 'attachView']);
        Route::delete('/delete/{id}', [NoticeController::class, 'delete']);
        Route::get('/view/{id}', [NoticeController::class, 'viewNotice']);
        Route::put('/update/{id}', [NoticeController::class, 'updateNotice']);
        // Route::get('/show-all-notices', [NoticeController::class, 'show']);
    });
});










// ======================
// Slider Routes
// ======================
// Route::middleware('auth:sanctum')->group(function () {
//     Route::prefix('slider')->group(function () {
//         Route::get('/', [SliderController::class, 'index']);
//         Route::post('/create', [SliderController::class, 'store']);
//         Route::delete('/delete/{id}', [SliderController::class, 'delete']);
//     });
// });

// Route::prefix('slider')->group(function () {
//     Route::get('/public', [SliderController::class, 'show']);
// });










Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('expense')->group(function () {
        Route::get('/', [ExpenseController::class, 'index']);
        Route::get('/get-subcategory/{id}', [ExpenseController::class, 'getSubCategory']);
        Route::post('/create', [ExpenseController::class, 'store']);
        Route::get('/details/{id}', [ExpenseController::class, 'detailsShow']);
        Route::get('/print/{id}', [ExpenseController::class, 'print']);
        Route::delete('/delete/{id}', [ExpenseController::class, 'delete']);
        // Expense setting routes
        Route::get('/setting', [ExpenseController::class, 'setting']);
        Route::post('/category', [ExpenseController::class, 'storeCategory']);
        Route::post('/subcategory', [ExpenseController::class, 'storeSubCategory']);
        Route::delete('/category/{id}', [ExpenseController::class, 'deleteCategory']);
        Route::put('/edit/category/{id}', [ExpenseController::class, 'editCategory']);
        Route::delete('/subcategory/{id}', [ExpenseController::class, 'deleteSubCategory']);
        Route::put('/edit/subcategory/{id}', [ExpenseController::class, 'editSubCategory']);
    });
});








// ======================
// Super Admin Routes
// ======================
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('super-admin')->group(function () {
        Route::get('/', [ AdminController::class, 'index']);
        Route::get('/transaction', [ AdminController::class, 'transaction']);

        Route::get('/star-club/users', [AdminController::class, 'starClubUsers']);
        Route::post('/star-club/add-money/{user_id}', [AdminController::class, 'addMoneyStarClub']);

        Route::get('/dynamic-club/users', [AdminController::class, 'dynamicClubUsers']);
        Route::post('/dynamic-club/add-money/{user_id}', [AdminController::class, 'addMoneyDynamicClub']);

        Route::post('/add-money/{user_id}', [AdminController::class, 'addMoney']);
        Route::post('/deduct-money/{user_id}', [AdminController::class, 'deductMoney']);
        Route::get('/user/{user_id}', [AdminController::class, 'getUserDetails']);
        Route::put('/user/change-role', [AdminController::class, 'changeUserRole']);
    });
});
