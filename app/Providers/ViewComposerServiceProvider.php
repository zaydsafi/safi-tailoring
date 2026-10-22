<?php

namespace App\Providers;

use App\Models\Setting;
use App\Support\Cart;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $view->with('shopSettings', $this->settings());
            $view->with('cartCount', app(Cart::class)->count());
        });
    }

    private function settings(): array
    {
        return Setting::viewSettings();
    }
}
