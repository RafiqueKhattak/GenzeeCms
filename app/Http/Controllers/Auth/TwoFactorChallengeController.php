<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactor\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The post-password, pre-session 2FA code prompt. AuthenticatedSessionController
 * verifies the password and, for a 2FA-enabled account, immediately logs the
 * user back out and stashes their id in session under 'login.2fa.user_id'
 * rather than completing Auth::login() — this controller is what actually
 * finishes the login once the TOTP (or a recovery) code checks out.
 */
class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        if (! $request->session()->has('login.2fa.user_id')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/TwoFactorChallenge');
    }

    public function store(Request $request, TotpService $totp): RedirectResponse
    {
        $userId = $request->session()->get('login.2fa.user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $throttleKey = 'two-factor:'.$userId.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors(['code' => 'Too many attempts. Try again in a minute.']);
        }

        $request->validate([
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $user = User::find($userId);
        if (! $user || ! $user->hasEnabledTwoFactor()) {
            $request->session()->forget(['login.2fa.user_id', 'login.2fa.remember']);

            return redirect()->route('login');
        }

        $valid = false;

        if ($request->filled('code') && $totp->verify($user->two_factor_secret, $request->string('code'))) {
            $valid = true;
        } elseif ($request->filled('recovery_code')) {
            $codes = $user->two_factor_recovery_codes ?? [];
            $submitted = trim((string) $request->input('recovery_code'));

            if (in_array($submitted, $codes, true)) {
                $valid = true;
                $user->two_factor_recovery_codes = array_values(array_diff($codes, [$submitted]));
                $user->save();
            }
        }

        if (! $valid) {
            RateLimiter::hit($throttleKey);

            return back()->withErrors(['code' => 'That code was not recognised.']);
        }

        RateLimiter::clear($throttleKey);

        $remember = (bool) $request->session()->pull('login.2fa.remember', false);
        $request->session()->forget('login.2fa.user_id');
        $request->session()->regenerate();

        Auth::login($user, $remember);

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }
}
