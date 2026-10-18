<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\VerificationCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_and_issues_otp_then_marks_verified(): void
    {
        Setting::set('otp_required', '1');
        Setting::set('otp_via_whatsapp', '0');
        Setting::set('otp_via_email', '1');
        Setting::set('notify_whatsapp_enabled', '0');

        Mail::fake();

        $this->withSession(['locale' => 'en'])
            ->post(route('register.store'), [
                'name' => 'Test Customer',
                'email' => 'otp@example.test',
                'phone' => '0700123456',
                'password' => 'long-enough-password',
                'password_confirmation' => 'long-enough-password',
            ])
            ->assertRedirect(route('verification.show'));

        $user = \App\Models\User::where('email', 'otp@example.test')->firstOrFail();

        $this->assertNull($user->email_verified_at);
        Mail::assertSent(\App\Mail\OtpMail::class);

        // Wrong code rejected…
        $this->withSession(['locale' => 'en'])
            ->actingAs($user)
            ->post(route('verification.check'), ['code' => '000000'])
            ->assertRedirect();
        $this->assertNull($user->fresh()->email_verified_at);

        // …the real one verifies and logs the customer into their orders.
        $code = $this->issuedPlainCode($user, 1);

        $this->withSession(['locale' => 'en'])
            ->actingAs($user)
            ->post(route('verification.check'), ['code' => $code])
            ->assertRedirect(route('account.orders'));

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertNotNull($user->fresh()->phone_verified_at);
    }

    public function test_login_redirects_unverified_to_verification(): void
    {
        Setting::set('otp_required', '1');
        Setting::set('otp_via_whatsapp', '0');
        Setting::set('otp_via_email', '1');

        $this->withSession(['locale' => 'en'])
            ->post(route('register.store'), [
                'name' => 'Gate Customer',
                'email' => 'gate@example.test',
                'phone' => '0700654321',
                'password' => 'long-enough-password',
                'password_confirmation' => 'long-enough-password',
            ])
            ->assertRedirect(route('verification.show'));

        auth()->logout();

        $this->withSession(['locale' => 'en'])
            ->post(route('login.store'), [
                'login' => 'gate@example.test',
                'password' => 'long-enough-password',
            ])
            ->assertRedirect(route('verification.show'));
    }

    public function test_resend_respects_cooldown(): void
    {
        Setting::set('otp_required', '1');
        Setting::set('otp_via_whatsapp', '0');
        Setting::set('otp_via_email', '1');

        Mail::fake();

        $this->withSession(['locale' => 'en'])
            ->post(route('register.store'), [
                'name' => 'Cooldown Customer',
                'email' => 'cooldown@example.test',
                'phone' => '07001112222',
                'password' => 'long-enough-password',
                'password_confirmation' => 'long-enough-password',
            ])
            ->assertRedirect(route('verification.show'));

        $user = \App\Models\User::where('email', 'cooldown@example.test')->firstOrFail();

        $count = VerificationCode::where('user_id', $user->id)->count();

        $this->withSession(['locale' => 'en'])
            ->actingAs($user)
            ->post(route('verification.resend'))
            ->assertRedirect();

        $this->assertSame($count, VerificationCode::where('user_id', $user->id)->count());
        Mail::assertSent(\App\Mail\OtpMail::class, 1);
    }

    /**
     * The full code lives only inside the mail content; read it from the
     * mailables that went through Mail::fake().
     */
    private function issuedPlainCode($user, int $index): ?string
    {
        $mailable = Mail::sent(\App\Mail\OtpMail::class)[$index - 1] ?? null;

        return $mailable?->code;
    }
}
