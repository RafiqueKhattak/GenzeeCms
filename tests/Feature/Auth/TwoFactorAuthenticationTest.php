<?php

use App\Models\User;
use App\Services\TwoFactor\TotpService;

test('login redirects straight to dashboard when 2FA is not enabled', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('login with a 2FA-enabled account does not authenticate until a valid code is submitted', function () {
    $totp = new TotpService;
    $secret = $totp->generateSecret();

    $user = User::factory()->create();
    $user->two_factor_secret = $secret;
    $user->two_factor_recovery_codes = ['RECOVERYCODE1'];
    $user->two_factor_confirmed_at = now();
    $user->save();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    // Password was correct, but the session must NOT be authenticated yet.
    $this->assertGuest();
    $response->assertRedirect(route('two-factor.challenge'));

    // Wrong code: still guest.
    $this->post('/two-factor-challenge', ['code' => '000000'])
        ->assertSessionHasErrors('code');
    $this->assertGuest();

    // Correct TOTP code: now authenticated.
    $counter = intdiv(time(), 30);
    $validCode = (new ReflectionMethod($totp, 'codeAt'))->invoke($totp, $secret, $counter);

    $response = $this->post('/two-factor-challenge', ['code' => $validCode]);

    $this->assertAuthenticatedAs($user->fresh());
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('a recovery code can be used once to complete a 2FA login', function () {
    $totp = new TotpService;

    $user = User::factory()->create();
    $user->two_factor_secret = $totp->generateSecret();
    $user->two_factor_recovery_codes = ['ONETIMECODE1', 'ONETIMECODE2'];
    $user->two_factor_confirmed_at = now();
    $user->save();

    $this->post('/login', ['email' => $user->email, 'password' => 'password']);
    $this->assertGuest();

    $this->post('/two-factor-challenge', ['recovery_code' => 'ONETIMECODE1']);
    $this->assertAuthenticatedAs($user->fresh());

    expect($user->fresh()->two_factor_recovery_codes)->toBe(['ONETIMECODE2']);

    // The same recovery code cannot be reused.
    auth()->logout();
    $this->post('/login', ['email' => $user->email, 'password' => 'password']);
    $this->post('/two-factor-challenge', ['recovery_code' => 'ONETIMECODE1'])
        ->assertSessionHasErrors('code');
    $this->assertGuest();
});
