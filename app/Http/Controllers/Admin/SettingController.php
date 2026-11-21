<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ShopTestMail;
use App\Models\EmailDelivery;
use App\Models\Setting;
use App\Support\EmailLog;
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
        // A pasted profile link is often written without a scheme
        // ("facebook.com/shop"); the browser's url check would then refuse to
        // submit the whole form with no visible explanation, so normalise the
        // value here instead of rejecting the save.
        $request->merge([
            'facebook_url' => $this->normalizeUrl($request->input('facebook_url')),
            'instagram_url' => $this->normalizeUrl($request->input('instagram_url')),
            'shop_email' => trim((string) $request->input('shop_email')) ?: null,
        ]);

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
            'whatsapp_auth_template' => ['nullable', 'string', 'max:191'],

            // SEO & analytics integrations.
            'google_analytics_id' => ['nullable', 'string', 'max:32', 'regex:/^G-[A-Z0-9]{5,}$/i'],
            'google_site_verification' => ['nullable', 'string', 'max:255'],
            'bing_site_verification' => ['nullable', 'string', 'max:255'],

            // Outgoing email. A filled host switches mail to SMTP; blank
            // fields fall back to whatever .env provides.
            'mail_smtp_host' => ['nullable', 'string', 'max:255'],
            'mail_smtp_port' => ['nullable', 'integer', 'between:1,65535'],
            'mail_smtp_username' => ['nullable', 'string', 'max:255'],
            'mail_smtp_password' => ['nullable', 'string', 'max:255'],
            'mail_smtp_encryption' => ['nullable', 'in:tls,ssl,none'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],

            // Off-site backups (cPanel hosting over FTP/FTPS).
            'offsite_ftp_host' => ['nullable', 'string', 'max:255'],
            'offsite_ftp_port' => ['nullable', 'integer', 'between:1,65535'],
            'offsite_ftp_username' => ['nullable', 'string', 'max:255'],
            'offsite_ftp_password' => ['nullable', 'string', 'max:255'],
            'offsite_ftp_secure' => ['nullable', 'in:none,ssl'],
            'offsite_ftp_path' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        foreach (['hesabpay_enabled', 'whatsapp_cloud_enabled', 'notify_email_enabled', 'notify_whatsapp_enabled', 'otp_required', 'otp_via_whatsapp', 'otp_via_email', 'offsite_backup_enabled'] as $flag) {
            Setting::set($flag, $request->boolean($flag) ? '1' : '0');
        }

        // The form is several screens long, so land on the save row itself:
        // the owner sees the confirmation right where the button was pressed
        // instead of being dropped back at the top of the page.
        return redirect()
            ->to(route('admin.settings.edit').'#settings-save')
            ->with('success', t('admin.flash.settings_saved', 'Settings saved.'));
    }

    /**
     * Turns "facebook.com/page", "//facebook.com/page" or an empty string
     * into a value the url rule accepts, or null when nothing was entered.
     */
    private function normalizeUrl(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (! preg_match('#^https?://#i', $value)) {
            return 'https://'.ltrim($value, '/');
        }

        return $value;
    }

    /**
     * Verifies the configured mail delivery with one real message so the
     * shop owner can see "it works" or the exact error before going live.
     */
    public function testEmail(Request $request)
    {
        $data = $request->validate([
            'test_email_to' => ['required', 'email', 'max:255'],
        ]);

        $delivery = EmailLog::send($data['test_email_to'], new ShopTestMail);

        if ($delivery->status === EmailDelivery::STATUS_SENT) {
            return back()->with('success', t('admin.settings.test_email_sent',
                'Test email sent to :email — check the inbox (and spam folder).', ['email' => $data['test_email_to']]));
        }

        return back()->with('error', t('admin.settings.test_email_failed',
            'Sending failed: :error', ['error' => $delivery->error ?? 'Unknown error']));
    }
}
