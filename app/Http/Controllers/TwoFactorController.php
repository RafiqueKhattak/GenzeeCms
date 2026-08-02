<?php

namespace App\Http\Controllers;

use App\Services\TwoFactor\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Self-service TOTP 2FA management from the Profile page — separate from
 * TwoFactorChallengeController, which handles the login-time code prompt.
 * Opt-in per account (any role), not enforced site-wide, so it doesn't risk
 * locking anyone out of this solo/small-team-maintained admin panel.
 */
class TwoFactorController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();

        $setupSecret = null;
        $qrUri = null;

        if (! $user->hasEnabledTwoFactor()) {
            $setupSecret = $request->session()->get('2fa.setup_secret') ?? (new TotpService)->generateSecret();
            $request->session()->put('2fa.setup_secret', $setupSecret);
            $qrUri = (new TotpService)->provisioningUri($setupSecret, $user->email, config('app.name'));
        }

        return Inertia::render('Profile/TwoFactor', [
            'enabled' => $user->hasEnabledTwoFactor(),
            'setupSecret' => $setupSecret,
            'qrUri' => $qrUri,
            'recoveryCodes' => $request->session()->get('2fa.recovery_codes_to_show'),
        ]);
    }

    public function enable(Request $request, TotpService $totp): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $secret = $request->session()->get('2fa.setup_secret');
        if (! $secret || ! $totp->verify($secret, $request->string('code'))) {
            return back()->withErrors(['code' => 'That code is incorrect or expired — try the current 6-digit code from your app.']);
        }

        $user = $request->user();
        $recoveryCodes = collect(range(1, 8))->map(fn () => Str::random(10))->all();

        $user->two_factor_secret = $secret;
        $user->two_factor_recovery_codes = $recoveryCodes;
        $user->two_factor_confirmed_at = now();
        $user->save();

        $request->session()->forget('2fa.setup_secret');
        $request->session()->flash('2fa.recovery_codes_to_show', $recoveryCodes);

        return redirect()->route('two-factor.show')->with('success', 'Two-factor authentication enabled. Save your recovery codes below — they will not be shown again.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['current_password' => ['required', 'current_password']]);

        $user = $request->user();
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return redirect()->route('two-factor.show')->with('success', 'Two-factor authentication disabled.');
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $request->validate(['current_password' => ['required', 'current_password']]);

        $user = $request->user();
        if (! $user->hasEnabledTwoFactor()) {
            return back()->with('error', 'Two-factor authentication is not enabled.');
        }

        $recoveryCodes = collect(range(1, 8))->map(fn () => Str::random(10))->all();
        $user->two_factor_recovery_codes = $recoveryCodes;
        $user->save();

        $request->session()->flash('2fa.recovery_codes_to_show', $recoveryCodes);

        return redirect()->route('two-factor.show')->with('success', 'New recovery codes generated — save them below, they will not be shown again.');
    }
}
