<?php

namespace App\Services\TwoFactor;

/**
 * Minimal RFC 6238 TOTP implementation (HMAC-SHA1, 30s step, 6 digits) —
 * compatible with Google Authenticator, Authy, 1Password, etc. Deliberately
 * dependency-free (no pragmarx/google2fa or similar) since the whole
 * algorithm is ~80 lines of well-specified, easily-tested math and this
 * avoids pulling a new composer package into a production auth path.
 */
class TotpService
{
    protected const DIGITS = 6;

    protected const PERIOD = 30;

    protected const SECRET_BYTES = 20; // 160-bit secret, standard for TOTP

    public function generateSecret(): string
    {
        return $this->base32Encode(random_bytes(self::SECRET_BYTES));
    }

    public function provisioningUri(string $secret, string $email, string $issuer): string
    {
        $label = rawurlencode($issuer.':'.$email);
        $params = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => self::DIGITS,
            'period' => self::PERIOD,
        ], '', '&', PHP_QUERY_RFC3986);

        return "otpauth://totp/{$label}?{$params}";
    }

    /**
     * Accepts a code valid for the current 30s step or one step either side
     * (90s window total) to tolerate clock drift between server and phone.
     */
    public function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\D/', '', (string) $code);
        if (strlen($code) !== self::DIGITS) {
            return false;
        }

        $timestamp = time();

        for ($drift = -1; $drift <= 1; $drift++) {
            $counter = intdiv($timestamp, self::PERIOD) + $drift;
            if (hash_equals($this->codeAt($secret, $counter), $code)) {
                return true;
            }
        }

        return false;
    }

    protected function codeAt(string $secret, int $counter): string
    {
        $key = $this->base32Decode($secret);
        $binaryCounter = pack('N*', 0, $counter); // 8-byte big-endian counter (RFC 4226)

        $hash = hash_hmac('sha1', $binaryCounter, $key, true);
        $offset = ord($hash[19]) & 0x0F;

        $truncated = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($truncated % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    protected function base32Encode(string $binary): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        foreach (str_split($binary) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }

        $output = '';
        foreach (str_split($bits, 5) as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $output .= $alphabet[bindec($chunk)];
        }

        return $output;
    }

    protected function base32Decode(string $base32): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32 = strtoupper(rtrim($base32, '='));

        $bits = '';
        foreach (str_split($base32) as $char) {
            $pos = strpos($alphabet, $char);
            if ($pos === false) {
                continue;
            }
            $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $binary = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) < 8) {
                continue;
            }
            $binary .= chr(bindec($byte));
        }

        return $binary;
    }
}
