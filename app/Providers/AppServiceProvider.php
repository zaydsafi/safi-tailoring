<?php

namespace App\Providers;

use App\Support\Locales;
use App\Support\MailerSettings;
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

        // SMTP saved in the admin Settings page wins over the .env defaults,
        // so the shop can fix mail delivery without touching config files.
        // Web requests only — boot also runs for artisan (migrations run
        // before the settings table exists) and a missing table must never
        // take the whole app down.
        if (! app()->runningInConsole()) {
            try {
                MailerSettings::apply();
            } catch (\Throwable) {
                // Fall back to the .env mailer.
            }
        }
    }
}
