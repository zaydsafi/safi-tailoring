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
        return [
            'shop_name' => Setting::get('shop_name'),
            'shop_tagline' => Setting::get('shop_tagline'),
            'shop_phone' => Setting::get('shop_phone'),
            'shop_email' => Setting::get('shop_email'),
            'shop_address' => Setting::get('shop_address'),
            'currency_symbol' => Setting::get('currency_symbol'),
            'announcement' => Setting::get('announcement'),
            'facebook_url' => Setting::get('facebook_url'),
            'instagram_url' => Setting::get('instagram_url'),
            'whatsapp_number' => Setting::get('whatsapp_number'),
            'hero_title' => Setting::get('hero_title'),
            'hero_subtitle' => Setting::get('hero_subtitle'),
        ];
    }
}
