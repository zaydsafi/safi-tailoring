<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomOrderRequestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MeasurementProfileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\AdminLocale;
use App\Http\Middleware\SetLocale;
use App\Support\Locales;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Root: send visitors to their preferred language
|--------------------------------------------------------------------------
*/
Route::get('/', [LocaleController::class, 'root'])->name('root');

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
        Route::get('/order/{order}/receipt', [CheckoutController::class, 'receipt'])->name('orders.guest_invoice');

        Route::get('/custom-order', [CustomOrderRequestController::class, 'show'])->name('custom-order.request');
        Route::post('/custom-order', [CustomOrderRequestController::class, 'store'])->name('custom-order.request.store');
        // Public: guests submit requests too, and the thank-you page only
        // shows the non-sensitive reference number of the new request.
        Route::get('/custom-order/thankyou/{requestRecord}', [CustomOrderRequestController::class, 'thankyou'])->name('custom-order.request.thankyou');

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

        // OTP verification needs the session created at registration.
        // The controller double-checks eligibility (unverified customer +
        // OTP requirement on + at least one deliverable channel).
        Route::get('/verify', [AuthController::class, 'showVerify'])->name('verification.show');
        Route::post('/verify', [AuthController::class, 'verify'])->name('verification.check');
        Route::post('/verify/resend', [AuthController::class, 'resend'])->name('verification.resend');

        Route::middleware(['auth', 'verified.customer'])->prefix('account')->name('account.')->group(function () {
            Route::get('/orders', [OrderController::class, 'index'])->name('orders');
            Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
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
| Admin panel (English / Pashto / Persian — switcher in the admin header)
|--------------------------------------------------------------------------
*/
Route::get('/admin/lang/{locale}', [LocaleController::class, 'switchAdmin'])->name('admin.lang');

Route::get('/admin/login', [Admin\AuthController::class, 'showLogin'])->name('admin.login')->middleware(['guest', AdminLocale::class]);
Route::post('/admin/login', [Admin\AuthController::class, 'login'])->name('admin.login.store')->middleware(['guest', AdminLocale::class]);

Route::middleware(['admin', AdminLocale::class])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [Admin\AuthController::class, 'logout'])->name('logout');
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('roles', Admin\RoleController::class)->except(['show'])->middleware('perm:roles');

    // Static paths must be registered before the {requestRecord} wildcard
    // route, otherwise “create-for-customer” is swallowed by it.
    Route::get('/custom-orders', [Admin\CustomOrderRequestController::class, 'index'])->name('custom-orders.index')->middleware('perm:custom_orders');
    Route::get('/custom-orders/create-for-customer', [Admin\CustomerOrderController::class, 'create'])->name('custom-orders.create')->middleware('perm:custom_orders');
    Route::post('/custom-orders/create-for-customer', [Admin\CustomerOrderController::class, 'store'])->name('custom-orders.store')->middleware('perm:custom_orders');

    Route::get('/custom-orders/{requestRecord}', [Admin\CustomOrderRequestController::class, 'show'])->name('custom-orders.show')->middleware('perm:custom_orders');
    Route::patch('/custom-orders/{requestRecord}/status', [Admin\CustomOrderRequestController::class, 'updateStatus'])->name('custom-orders.status')->middleware('perm:custom_orders');
    Route::post('/custom-orders/{requestRecord}/convert', [Admin\CustomOrderRequestController::class, 'convertToOrder'])->name('custom-orders.convert')->middleware('perm:custom_orders');

    Route::get('/tailor', [Admin\TailorDashboardController::class, 'index'])->name('tailor.dashboard')->middleware('perm:tailor_dashboard');
    Route::get('/tailor/requests', [Admin\TailorDashboardController::class, 'requests'])->name('tailor.requests')->middleware('perm:tailor_dashboard');

    Route::resource('categories', Admin\CategoryController::class)->except(['show'])->middleware('perm:categories');
    Route::resource('products', Admin\ProductController::class)->except(['show'])->middleware('perm:products');

    // Excel / CSV report downloads
    Route::get('/exports/orders', [Admin\ExportController::class, 'orders'])->name('exports.orders')->middleware('perm:orders');
    Route::get('/exports/sales-summary', [Admin\ExportController::class, 'salesSummary'])->name('exports.sales_summary')->middleware('perm:orders');
    Route::get('/exports/products', [Admin\ExportController::class, 'products'])->name('exports.products')->middleware('perm:products');
    Route::get('/exports/customers', [Admin\ExportController::class, 'customers'])->name('exports.customers')->middleware('perm:users');
    Route::get('/exports/custom-orders', [Admin\ExportController::class, 'customOrders'])->name('exports.custom_orders')->middleware('perm:custom_orders');
    Route::get('/exports/messages', [Admin\ExportController::class, 'messages'])->name('exports.messages')->middleware('perm:messages');
    Route::get('/exports/email-log', [Admin\ExportController::class, 'emailLog'])->name('exports.email_log')->middleware('perm:settings');

    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index')->middleware('perm:orders');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show')->middleware('perm:orders');
    Route::get('/orders/{order}/invoice', [Admin\OrderController::class, 'invoice'])->name('orders.invoice')->middleware('perm:orders');
    Route::patch('/orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.status')->middleware('perm:orders');
    Route::patch('/orders/{order}/payment', [Admin\OrderController::class, 'updatePayment'])->name('orders.payment')->middleware('perm:orders');
    Route::delete('/orders/{order}', [Admin\OrderController::class, 'destroy'])->name('orders.destroy')->middleware('perm:orders');

    Route::resource('users', Admin\UserController::class)->except(['show'])->middleware('perm:users');
    Route::patch('/users/{user}/block', [Admin\UserController::class, 'block'])->name('users.block')->middleware('perm:users');
    Route::patch('/users/{user}/unblock', [Admin\UserController::class, 'unblock'])->name('users.unblock')->middleware('perm:users');

    Route::get('/customers/{customer}', [Admin\CustomerController::class, 'show'])->name('customers.show')->middleware('perm:users');

    Route::prefix('translations')->middleware('perm:translations')->group(function () {
        Route::get('/', [Admin\TranslationController::class, 'index'])->name('translations.index');
        Route::get('/missing', [Admin\TranslationController::class, 'missing'])->name('translations.missing');
        Route::get('/create', [Admin\TranslationController::class, 'create'])->name('translations.create');
        Route::post('/', [Admin\TranslationController::class, 'store'])->name('translations.store');
        Route::get('/{translation}/edit', [Admin\TranslationController::class, 'edit'])->name('translations.edit');
        Route::put('/{translation}', [Admin\TranslationController::class, 'update'])->name('translations.update');
        Route::delete('/{translation}', [Admin\TranslationController::class, 'destroy'])->name('translations.destroy');
    });
    Route::get('/translations/missing', [Admin\TranslationController::class, 'missing'])->name('translations.missing')->middleware('perm:translations');
    Route::get('/translations/create', [Admin\TranslationController::class, 'create'])->name('translations.create')->middleware('perm:translations');
    Route::get('/messages', [Admin\MessageController::class, 'index'])->name('messages.index')->middleware('perm:messages');
    Route::get('/messages/{message}', [Admin\MessageController::class, 'show'])->name('messages.show')->middleware('perm:messages');
    Route::delete('/messages/{message}', [Admin\MessageController::class, 'destroy'])->name('messages.destroy')->middleware('perm:messages');

    Route::get('/email-log', [Admin\EmailLogController::class, 'index'])->name('email-log.index')->middleware('perm:settings');

    // Database backups: list / create / download / delete / restore.
    Route::prefix('backups')->name('backups.')->middleware('perm:settings')->group(function () {
        Route::get('/', [Admin\BackupController::class, 'index'])->name('index');
        Route::post('/', [Admin\BackupController::class, 'store'])->name('store');
        Route::get('/{name}/download', [Admin\BackupController::class, 'download'])->name('download')->where('name', '[A-Za-z0-9._\-]+');
        Route::get('/{name}/recover', [Admin\BackupController::class, 'recover'])->name('recover')->where('name', '[A-Za-z0-9._\-]+');
        Route::post('/{name}/restore', [Admin\BackupController::class, 'restore'])->name('restore')->where('name', '[A-Za-z0-9._\-]+');
        Route::delete('/{name}', [Admin\BackupController::class, 'destroy'])->name('destroy')->where('name', '[A-Za-z0-9._\-]+');
    });

    Route::get('/settings', [Admin\SettingController::class, 'edit'])->name('settings.edit')->middleware('perm:settings');
    Route::put('/settings', [Admin\SettingController::class, 'update'])->name('settings.update')->middleware('perm:settings');
    Route::post('/settings/test-email', [Admin\SettingController::class, 'testEmail'])->name('settings.test_email')->middleware('perm:settings');
});
