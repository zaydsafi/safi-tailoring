<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MeasurementProfileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\SetLocale;
use App\Support\Locales;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Root: send visitors to their preferred language
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $locale = (string) session('locale', Locales::DEFAULT);

    return redirect('/' . (array_key_exists($locale, Locales::SUPPORTED) ? $locale : Locales::DEFAULT));
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

/*
|--------------------------------------------------------------------------
| Localized storefront (English / Pashto / Persian)
|--------------------------------------------------------------------------
*/
Route::prefix('{locale}')
    ->whereIn('locale', Locales::codes())
    ->middleware(SetLocale::class)
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
        Route::get('/product/{slug}', [ShopController::class, 'show'])->name('shop.show');
        Route::get('/about', [PageController::class, 'about'])->name('about');
        Route::get('/contact', [ContactController::class, 'show'])->name('contact');
        Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart', [CartController::class, 'add'])->name('cart.add');
        Route::patch('/cart/{key}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/{key}', [CartController::class, 'remove'])->name('cart.remove');

        Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::get('/order/{order}/success', [CheckoutController::class, 'success'])->name('orders.success');

        Route::get('/track-order', [OrderController::class, 'trackForm'])->name('orders.track');
        Route::post('/track-order', [OrderController::class, 'track'])->name('orders.track.lookup');

        // HesabPay browser redirects keep the shopper's language; the
        // server-to-server webhook stays un-prefixed (see below).
        Route::get('/payment/hesabpay/return/{orderNumber}/{nonce?}', [PaymentController::class, 'return'])->name('hesabpay.return');
        Route::get('/payment/hesabpay/cancel/{orderNumber}', [PaymentController::class, 'cancel'])->name('hesabpay.cancel');
        Route::post('/payment/hesabpay/retry/{order}', [PaymentController::class, 'retry'])->name('hesabpay.retry');

        Route::middleware('guest')->group(function () {
            Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
            Route::post('/login', [AuthController::class, 'login'])->name('login.store');
            Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
            Route::post('/register', [AuthController::class, 'register'])->name('register.store');

            Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
            Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
            Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
            Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
        });
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

        Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
            Route::get('/orders', [OrderController::class, 'index'])->name('orders');
            Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::get('/profile', [AccountController::class, 'editProfile'])->name('profile');
            Route::put('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
            Route::put('/profile/password', [AccountController::class, 'updatePassword'])->name('password.update');
            Route::get('/measurements', [MeasurementProfileController::class, 'index'])->name('measurements');
            Route::post('/measurements', [MeasurementProfileController::class, 'store'])->name('measurements.store');
            Route::put('/measurements/{measurementProfile}', [MeasurementProfileController::class, 'update'])->name('measurements.update');
            Route::delete('/measurements/{measurementProfile}', [MeasurementProfileController::class, 'destroy'])->name('measurements.destroy');
        });
    });

/*
|--------------------------------------------------------------------------
| HesabPay webhook (fixed public URL — no locale, no CSRF)
|--------------------------------------------------------------------------
*/
Route::post('/webhooks/hesabpay', [PaymentController::class, 'webhook'])->name('webhooks.hesabpay');

/*
|--------------------------------------------------------------------------
| Admin panel (English only)
|--------------------------------------------------------------------------
*/
Route::get('/admin/login', [Admin\AuthController::class, 'showLogin'])->name('admin.login')->middleware('guest');
Route::post('/admin/login', [Admin\AuthController::class, 'login'])->name('admin.login.store')->middleware('guest');

Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [Admin\AuthController::class, 'logout'])->name('logout');
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('categories', Admin\CategoryController::class)->except(['show']);
    Route::resource('products', Admin\ProductController::class)->except(['show']);

    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('/orders/{order}/payment', [Admin\OrderController::class, 'updatePayment'])->name('orders.payment');
    Route::delete('/orders/{order}', [Admin\OrderController::class, 'destroy'])->name('orders.destroy');

    Route::resource('users', Admin\UserController::class)->except(['show']);
    Route::patch('/users/{user}/block', [Admin\UserController::class, 'block'])->name('users.block');
    Route::patch('/users/{user}/unblock', [Admin\UserController::class, 'unblock'])->name('users.unblock');

    Route::get('/customers/{customer}', [Admin\CustomerController::class, 'show'])->name('customers.show');

    Route::get('/translations', [Admin\TranslationController::class, 'index'])->name('translations.index');
    Route::get('/translations/create', [Admin\TranslationController::class, 'create'])->name('translations.create');
    Route::post('/translations', [Admin\TranslationController::class, 'store'])->name('translations.store');
    Route::get('/translations/{translation}/edit', [Admin\TranslationController::class, 'edit'])->name('translations.edit');
    Route::put('/translations/{translation}', [Admin\TranslationController::class, 'update'])->name('translations.update');
    Route::delete('/translations/{translation}', [Admin\TranslationController::class, 'destroy'])->name('translations.destroy');

    Route::get('/messages', [Admin\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{message}', [Admin\MessageController::class, 'show'])->name('messages.show');
    Route::delete('/messages/{message}', [Admin\MessageController::class, 'destroy'])->name('messages.destroy');

    Route::get('/settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [Admin\SettingController::class, 'update'])->name('settings.update');
});
