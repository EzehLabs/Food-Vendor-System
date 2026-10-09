<?php


use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AdminController;

use App\Http\Controllers\FoodController;
use App\Http\Controllers\OrderController;

use App\Http\Controllers\CartController;    

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;


// Email Verification Notice
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// Verify Email
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    // Redirect based on role
    if ($request->user()->role == 'admin') {
        return redirect('/admin/dashboard');
    }

    if ($request->user()->role == 'vendor') {
        return redirect('/vendor/dashboard');
    }

    return redirect('/customer/home');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/register/vendor', function () {
    return view('auth.register-vendor');
});

Route::get('/register/customer', function () {
    return view('auth.register-customer');
})->name('register.customer');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register/vendor', [RegisteredUserController::class, 'registerVendor']);
Route::post('/register/customer', [RegisteredUserController::class, 'registerCustomer']);

Route::middleware('auth')->group(function () {
    Route::post('/cart/add/{food_id}', [CartController::class, 'add']);
    Route::get('/cart', [CartController::class, 'index']);
    Route::get('/cart/remove/{id}', [CartController::class, 'remove']);
    Route::post('/cart/update/{id}', [CartController::class, 'update']);
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove']);
    Route::get('/cart/sidebar', [CartController::class,'sidebar']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});




// Route For Customer Order View
Route::get('/customer/orders', [OrderController::class, 'myOrders'])->name('customer.orders');
Route::get('/customer/orders/{id}', [OrderController::class, 'show'])
    ->name('customer.orders.show');
    
Route::get('/vendor/orders/{id}', [VendorController::class, 'showOrder'])
    ->name('vendor.orders.show');
Route::post('/vendor/order/{id}/status', [OrderController::class, 'updateStatus'])->name('vendor.order.status');
Route::post('/vendor/orders/{id}/status', [VendorController::class, 'updateOrderStatus'])
    ->name('vendor.orders.status');
    
// Route For Customer Transactions History
Route::get('/customer/transactions', [OrderController::class, 'transactions'])
    ->name('customer.transactions');

// Route::post('/checkout', [OrderController::class, 'checkout']);

Route::get('/checkout', [OrderController::class, 'showCheckout'])->name('checkout');

Route::post('/checkout', [OrderController::class, 'checkout'])->name('checkout.process');

Route::get('/payment/pay', [OrderController::class, 'redirectToGateway'])->name('payment.pay');

Route::get('/payment/callback', [OrderController::class, 'handleGatewayCallback'])->name('payment.callback');

// Route::get('/payment/{id}', [OrderController::class, 'showPayment'])->name('payment.show');
// Route::post('/payment/{id}', [OrderController::class, 'processPayment'])->name('payment.process');


Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/foods', [AdminController::class, 'foods'])->name('admin.foods');
    Route::get('/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::get('/orders/{id}', [AdminController::class, 'showOrder'])->name('admin.orders.show');
    Route::get('/payments', [AdminController::class, 'payments'])->name('admin.payments');
    
});

Route::middleware(['auth', 'verified', 'role:vendor'])->prefix('vendor')->group(function () {
    Route::get('/dashboard', [VendorController::class, 'index']);
    Route::get('/orders', [VendorController::class, 'orders'])->name('vendor.orders');
    // Route::resource('foods', FoodController::class);
    Route::get('/foods', [FoodController::class, 'index']);
    Route::get('/foods/create', [FoodController::class, 'create']);
    Route::post('/foods/store', [FoodController::class, 'store']);
    Route::get('/foods/delete/{id}', [FoodController::class, 'destroy']);
    Route::get('/vendor/foods/edit/{id}', [FoodController::class, 'edit'])->name('vendor.foods.edit');
    Route::post('/vendor/foods/update/{id}', [FoodController::class, 'update'])->name('vendor.foods.update');

});

Route::middleware(['auth', 'verified', 'role:customer'])->prefix('customer')->group(function () {
    Route::get('/home', [CustomerController::class, 'index']);
    Route::get('/home', [FoodController::class, 'customerView']);
});

/*
|--------------------------------------------------------------------------
| Forgot Password Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    // Show Forgot Password Form
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    // Send Password Reset Link
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // Show Reset Password Form
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    // Reset Password
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

require __DIR__.'/auth.php';
