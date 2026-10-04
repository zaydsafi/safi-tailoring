<?php

namespace App\Providers;

use App\Support\Locales;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Storefront routes carry a {locale} segment; the SetLocale middleware
        // overrides this per request. The default keeps route() calls made
        // outside the storefront (admin, webhooks, notifications) working.
        URL::defaults(['locale' => Locales::DEFAULT]);
    }
}
