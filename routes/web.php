<?php

use App\Http\Controllers\AccountDashboardController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMessageController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [ProductController::class, 'index'])->name('shop');
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/portfolio', [HomeController::class, 'portfolio'])->name('portfolio');
Route::get('/contacts', [ContactController::class, 'index'])->name('contacts');
Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [RegistrationController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->middleware('throttle:6,1')->name('register.store');
    Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'simulateRecovery'])->middleware('throttle:6,1')->name('password.simulate');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/account', AccountDashboardController::class)->name('account.dashboard');
    Route::get('/account/orders', [CustomerOrderController::class, 'index'])->name('account.orders');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/items/{product}', [CartController::class, 'store'])->name('cart.items.store');
    Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.items.update');
    Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.items.destroy');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/products/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/items/{wishlistItem}', [WishlistController::class, 'destroy'])->name('wishlist.items.destroy');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/orders/{order}', [CheckoutController::class, 'show'])->name('orders.show');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::patch('/products/{product}/status', [AdminProductController::class, 'updateStatus'])
        ->name('products.status');
    Route::resource('products', AdminProductController::class);

    Route::resource('categories', AdminCategoryController::class)->except('show');

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');

    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [AdminCustomerController::class, 'show'])->name('customers.show');

    Route::get('/messages', [AdminMessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{message}', [AdminMessageController::class, 'show'])->name('messages.show');
    Route::patch('/messages/{message}', [AdminMessageController::class, 'update'])->name('messages.update');
});
