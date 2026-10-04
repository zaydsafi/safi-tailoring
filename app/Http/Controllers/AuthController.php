<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.orders')->with('success', t('flash.welcome_prefix', 'Welcome to') . ' ' . config('app.name') . '!');
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
