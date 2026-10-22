<?php

namespace App\Support;

use App\Mail\OtpMail;
use App\Models\EmailDelivery;
use App\Models\Setting;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Hash;

class Otp
{
    public const TTL_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    public const RESEND_COOLDOWN_SECONDS = 60;

    public const PURPOSE_REGISTER = 'register';

    /**
     * Which channels the codes should travel through for this shop.
     * WhatsApp needs the Cloud API configured in settings; email needs
     * the user to have an address (registration collects one).
     *
     * @return list<string>
     */
    public static function channelsFor(User $user): array
    {
        $channels = [];

        if (Setting::get('otp_via_whatsapp') === '1') {
            $whatsapp = null;

            try {
                $whatsapp = WhatsApp::normalize($user->phone);
            } catch (\Throwable) {
                $whatsapp = null;
            }

            if (WhatsApp::cloudEnabled() && $whatsapp !== null) {
                $channels[] = 'whatsapp';
            }
        }

        if (Setting::get('otp_via_email') === '1' && filled($user->email)) {
            $channels[] = 'email';
        }

        return $channels;
    }

    /**
     * Is verification switched on and at least one channel able to deliver?
     */
    public static function required(User $user): bool
    {
        return $user->isVerified() === false
            && Setting::get('otp_required') === '1'
            && self::channelsFor($user) !== [];
    }

    public static function pending(User $user, string $purpose = self::PURPOSE_REGISTER): ?VerificationCode
    {
        return VerificationCode::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();
    }

    /**
     * Seconds until a new code may be issued, 0 when it may go out now.
     */
    public static function cooldownRemaining(VerificationCode $code): int
    {
        return max(0, self::RESEND_COOLDOWN_SECONDS - (int) $code->created_at->diffInSeconds(now()));
    }

    /**
     * Create a fresh code for the user (only when the cooldown passed) and
     * deliver it over every configured channel. Returns false while cooling
     * down or when nothing could be delivered.
     */
    public static function send(User $user, string $purpose = self::PURPOSE_REGISTER): bool
    {
        $code = self::pending($user, $purpose);

        if ($code !== null && self::cooldownRemaining($code) > 0) {
            return false;
        }

        if ($code !== null) {
            $code->update(['consumed_at' => now()]);
        }

        $channels = self::channelsFor($user);

        if ($channels === []) {
            return false;
        }

        $plain = (string) random_int(100000, 999999);
        $ttl = max(self::TTL_MINUTES, 1);

        VerificationCode::query()->create([
            'user_id' => $user->id,
            'purpose' => $purpose,
            'code_hash' => Hash::make($plain),
            'channels' => $channels,
            'expires_at' => now()->addMinutes($ttl),
        ]);

        $delivered = 0;

        foreach ($channels as $channel) {
            $delivered += self::deliver($channel, $user, $plain) ? 1 : 0;
        }

        if ($delivered === 0) {
            return false;
        }

        // Keep at most the last 5 codes per user to bound the table.
        VerificationCode::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->orderByDesc('id')
            ->skip(5)
            ->take(100)
            ->get()
            ->each(fn (VerificationCode $row) => $row->delete());

        return true;
    }

    /**
     * Check the code the user typed against the newest unconsumed code for
     * this purpose. Marks the attempt either way; on success marks verified
     * columns on the account for all channels of the code.
     */
    public static function consume(User $user, ?string $input, string $purpose = self::PURPOSE_REGISTER): bool
    {
        if (blank($input)) {
            return false;
        }

        $code = self::pending($user, $purpose);

        if ($code === null || now()->gt($code->expires_at)) {
            return false;
        }

        $code->increment('attempts');

        if ($code->attempts > self::MAX_ATTEMPTS) {
            return false;
        }

        if (! Hash::check($input, $code->code_hash)) {
            return false;
        }

        $code->update(['consumed_at' => now()]);

        $user->forceFill(['email_verified_at' => now(), 'phone_verified_at' => now()])->save();

        return true;
    }

    /**
     * Attempts left before the pending code locks.
     */
    public static function attemptsLeft(User $user, string $purpose = self::PURPOSE_REGISTER): int
    {
        $code = self::pending($user, $purpose);

        if ($code === null) {
            return 0;
        }

        return max(0, self::MAX_ATTEMPTS - $code->attempts);
    }

    private static function deliver(string $channel, User $user, string $code): bool
    {
        if ($channel === 'whatsapp') {
            $sent = WhatsApp::sendOtp($user->phone, $code, self::TTL_MINUTES);
        } else {
            $sent = false;
            $address = (string) ($user->email ?? '');

            if ($address !== '') {
                $delivery = EmailLog::send($address, new OtpMail($code, self::TTL_MINUTES, $user->name));
                $sent = $delivery->status === EmailDelivery::STATUS_SENT;
            }
        }

        return $sent;
    }
}
