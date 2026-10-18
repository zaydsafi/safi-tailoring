<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Otp;
use App\Support\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required'],
        ]);

        $user = $this->findUserByLogin(trim($data['login']));

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => __('auth.failed'),
            ]);
        }

        if ($user->isBlocked()) {
            throw ValidationException::withMessages([
                'login' => t('flash.account_suspended', 'Your account has been suspended. Please contact the shop for help.'),
            ]);
        }

        if ($user->isAdmin()) {
            throw ValidationException::withMessages([
                'login' => t('flash.admin_use_panel', 'Admin accounts must use the admin panel login.'),
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Accounts that still owe a verification code are guided there first.
        if (Otp::required($user)) {
            return redirect()->route('verification.show')
                ->with('error', t('flash.verify_needed', 'Please verify your account with the code we sent you.'));
        }

        return redirect()->intended(route('account.orders'));
    }

    private function findUserByLogin(string $login): ?User
    {
        if (str_contains($login, '@')) {
            return User::where('email', $login)->first();
        }

        $user = User::where('phone', $login)->first();
        if ($user) {
            return $user;
        }

        $digits = WhatsApp::normalize($login);
        if (!$digits) {
            return null;
        }

        $user = User::whereIn('phone', array_unique(array_filter([$digits, '+' . $digits, '00' . $digits])))
            ->get()
            ->first(fn (User $u) => WhatsApp::normalize($u->phone) === $digits);

        if (!$user && strlen($digits) >= 7) {
            $tail = substr($digits, -7);
            $user = User::whereNotNull('phone')
                ->where('phone', 'like', '%' . implode('%', str_split($tail)) . '%')
                ->get()
                ->first(fn (User $u) => WhatsApp::normalize($u->phone) === $digits);
        }

        return $user;
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->merge([
            'phone' => WhatsApp::normalize($request->input('phone')) ?? $request->input('phone'),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => 'customer',
            'email_verified_at' => null,
            'phone_verified_at' => null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        // Registration done — now the OTP gate (unless verification is
        // disabled or no channel can reach the customer).
        if (Otp::required($user)) {
            if (Otp::send($user)) {
                return redirect()->route('verification.show')->with('success', t('flash.otp_sent', 'We sent a 6-digit code — enter it below to finish signing up.'));
            }

            return redirect()->route('verification.show')->with('error', t('flash.otp_send_failed', 'We could not send the verification code right now — press "Resend code" to try again.'));
        }

        // Verification disabled: mark the account fully usable.
        $user->forceFill(['email_verified_at' => now(), 'phone_verified_at' => now()])->save();

        return redirect()->route('account.orders')->with('success', t('flash.welcome_prefix', 'Welcome to') . ' ' . config('app.name') . '!');
    }

    /**
     * OTP verification screen for the freshly registered (or unverified)
     * customer. Works with the logged-in session from registration; the
     * signed-out safety net is the account-track page, like every other
     * order flow.
     */
    public function showVerify(Request $request)
    {
        $user = $request->user();

        abort_unless($user !== null && Otp::required($user), 404);

        $code = Otp::pending($user);
        $seconds = $code ? Otp::cooldownRemaining($code) : 0;

        return view('auth.verify', [
            'channels' => Otp::channelsFor($user),
            'cooldown' => $seconds,
            'missingPhone' => blank($user->phone),
        ]);
    }

    public function verify(Request $request)
    {
        $user = $request->user();

        abort_unless($user !== null && Otp::required($user), 404);

        $data = $request->validate([
            'code' => ['required', 'digits_between:4,8'],
        ]);

        if (Otp::attemptsLeft($user) <= 0) {
            return back()->with('error', t('flash.otp_too_many', 'Too many wrong tries. Press "Resend code" for a fresh one.'));
        }

        if (!Otp::consume($user, $data['code'])) {
            return back()->with('error', t('flash.otp_wrong', 'Wrong or expired code. Check the code and try again.'));
        }

        return redirect()->route('account.orders')
            ->with('success', t('flash.account_verified', 'Your account is verified — welcome to :shop!', ['shop' => config('app.name')]));
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        abort_unless($user !== null && Otp::required($user), 404);

        if (!Otp::send($user)) {
            $code = Otp::pending($user);
            $seconds = $code ? Otp::cooldownRemaining($code) : 0;

            return back()->with('error', t('flash.otp_resend_wait', 'Please wait :seconds seconds, then try again.', ['seconds' => $seconds]));
        }

        return back()->with('success', t('flash.otp_resent', 'New code on its way — check your ' . implode(' and ', Otp::channelsFor($user)) . '.'));
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', t('flash.reset_link_sent', 'We emailed you a password reset link. Check your inbox (and spam folder).'));
        }

        if ($status === Password::RESET_THROTTLED) {
            return back()->with('error', t('flash.reset_link_throttled', 'Please wait a moment before requesting another reset link.'));
        }

        return back()->with('error', t('flash.reset_email_not_found', 'We could not find an account with that email address.'));
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => null,
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', t('flash.password_reset', 'Your password has been reset. You can sign in now.'));
        }

        if ($status === Password::INVALID_TOKEN) {
            return back()->with('error', t('flash.reset_token_invalid', 'This reset link is invalid or has expired. Please request a new one.'));
        }

        return back()->with('error', t('flash.reset_failed', 'We could not reset the password for that email address.'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', t('flash.signed_out', 'You have been signed out.'));
    }
}
