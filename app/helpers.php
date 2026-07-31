<?php

use App\Models\Setting;

if (! function_exists('canonical_url')) {
    function canonical_url(string $path): string
    {
        $base = rtrim(Setting::get('site_url', config('app.url')), '/');
        $path = '/'.ltrim($path, '/');

        return $base.$path;
    }
}
