<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $settings = [];
        foreach (array_keys(Setting::$defaults) as $key) {
            $settings[$key] = Setting::get($key);
        }

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'shop_name' => ['required', 'string', 'max:255'],
            'shop_tagline' => ['nullable', 'string', 'max:255'],
            'shop_phone' => ['nullable', 'string', 'max:50'],
            'shop_email' => ['nullable', 'email', 'max:255'],
            'shop_address' => ['nullable', 'string', 'max:500'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'free_delivery_over' => ['nullable', 'numeric', 'min:0'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:1000'],
            'announcement' => ['nullable', 'string', 'max:500'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'hesabpay_api_key' => ['nullable', 'string', 'max:500'],
            'hesabpay_environment' => ['required', 'in:production,sandbox'],
            'hesabpay_webhook_secret' => ['nullable', 'string', 'max:255'],
            'whatsapp_phone_number_id' => ['nullable', 'string', 'max:100'],
            'whatsapp_cloud_token' => ['nullable', 'string', 'max:1000'],
            'whatsapp_country_code' => ['nullable', 'string', 'max:5'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        foreach (['hesabpay_enabled', 'whatsapp_cloud_enabled'] as $flag) {
            Setting::set($flag, $request->boolean($flag) ? '1' : '0');
        }

        return back()->with('success', 'Settings saved.');
    }
}
