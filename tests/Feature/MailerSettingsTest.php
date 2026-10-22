<?php

namespace Tests\Feature;

use App\Mail\ShopTestMail;
use App\Models\Setting;
use App\Models\User;
use App\Support\MailerSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;
use Tests\TestCase;

class MailerSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_blank_host_keeps_env_mailer(): void
    {
        $original = config('mail.default');

        $this->assertNotSame('smtp', $original);

        MailerSettings::apply();

        $this->assertSame($original, config('mail.default'));
    }

    public function test_saved_smtp_overrides_mail_config_at_runtime(): void
    {
        Setting::set('mail_smtp_host', 'smtp.example.test');
        Setting::set('mail_smtp_port', '465');
        Setting::set('mail_smtp_username', 'shop@example.test');
        Setting::set('mail_smtp_password', 'secret');
        Setting::set('mail_smtp_encryption', 'ssl');
        Setting::set('mail_from_address', 'orders@example.test');
        Setting::set('mail_from_name', 'Safi Tailoring');

        MailerSettings::apply();

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.example.test', config('mail.mailers.smtp.host'));
        $this->assertSame(465, config('mail.mailers.smtp.port'));
        $this->assertSame('shop@example.test', config('mail.mailers.smtp.username'));
        $this->assertSame('secret', config('mail.mailers.smtp.password'));
        $this->assertSame('ssl', config('mail.mailers.smtp.encryption'));
        $this->assertSame('orders@example.test', config('mail.from.address'));
        $this->assertSame('Safi Tailoring', config('mail.from.name'));
    }

    public function test_settings_page_shows_smtp_fields_and_test_email_is_sent(): void
    {
        $admin = User::factory()->admin()->create();

        Mail::fake();

        $this->actingAs($admin)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Send test email')
            ->assertSee('mail_smtp_host', false);

        $this->actingAs($admin)
            ->post(route('admin.settings.test_email'), ['test_email_to' => 'customer@example.test'])
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(ShopTestMail::class, function (ShopTestMail $mail): bool {
            return $mail->hasTo('customer@example.test');
        });
    }

    public function test_test_email_reports_the_smtp_error(): void
    {
        $admin = User::factory()->admin()->create();

        Setting::set('mail_smtp_host', 'smtp.example.test');

        // A transport that always fails, so the catch path in the controller
        // is exercised and the raw error is shown to the admin.
        Mail::extend('array', function () {
            return new class implements TransportInterface
            {
                public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
                {
                    throw new \RuntimeException('SMTP connection failed');
                }

                public function __toString(): string
                {
                    return 'failing';
                }
            };
        });

        $this->actingAs($admin)
            ->post(route('admin.settings.test_email'), ['test_email_to' => 'customer@example.test'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertStringContainsString('SMTP connection failed', session('error'));
    }

    public function test_settings_update_persists_smtp_fields(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->validSettingsPayload([
                'mail_smtp_host' => 'smtp.gmail.com',
                'mail_smtp_port' => '587',
                'mail_smtp_username' => 'shop@example.test',
                'mail_smtp_password' => 'app-password',
                'mail_smtp_encryption' => 'tls',
                'mail_from_address' => 'orders@example.test',
                'mail_from_name' => 'Safi Tailoring Shop',
            ]))
            ->assertRedirect();

        $this->assertSame('smtp.gmail.com', Setting::get('mail_smtp_host'));
        $this->assertSame('587', Setting::get('mail_smtp_port'));
        $this->assertSame('tls', Setting::get('mail_smtp_encryption'));
        $this->assertSame('orders@example.test', Setting::get('mail_from_address'));
    }

    /**
     * The settings form validates a handful of required fields; fill those
     * and let every other key pass through blank.
     */
    private function validSettingsPayload(array $overrides = []): array
    {
        $payload = collect(array_keys(Setting::$defaults))
            ->mapWithKeys(fn (string $key) => [$key => ''])
            ->merge([
                'shop_name' => 'Safi Tailoring Shop',
                'currency_symbol' => 'AFN',
                'hesabpay_environment' => 'production',
            ])
            ->merge($overrides)
            ->all();

        return $payload;
    }
}
