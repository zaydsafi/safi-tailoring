<?php

namespace App\Http\Middleware;

use App\Support\Otp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keep unverified customer accounts out of account features until the
 * OTP check passes. Admins and verification-off shops pass through.
 */
class EnsureCustomerVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && Otp::required($user)) {
            return redirect()->route('verification.show')
                ->with('error', t('flash.verify_needed', 'Please verify your account with the code we sent you.'));
        }

        return $next($request);
    }
}
