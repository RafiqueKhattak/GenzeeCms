<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/Settings/Edit', [
            'settings' => Setting::all()->pluck('value', 'key'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'site_url' => ['required', 'url'],
            'meta_title_suffix' => ['nullable', 'string', 'max:100'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'string', 'max:500'],
            'google_analytics_id' => ['nullable', 'string', 'max:50'],
            'google_site_verification' => ['nullable', 'string', 'max:255'],
            'adsense_publisher_id' => ['nullable', 'string', 'max:50'],
            'ads_txt_content' => ['nullable', 'string', 'max:2000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:512'],
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('branding', 'public');
            $data['logo_path'] = '/storage/'.$path;
        }
        unset($data['logo']);

        if ($request->hasFile('favicon')) {
            $request->file('favicon')->move(public_path(), 'favicon.ico');
            $data['favicon_path'] = '/favicon.ico';
        }
        unset($data['favicon']);

        foreach ($data as $key => $value) {
            Setting::set($key, $value ?? '', $this->groupFor($key));
        }

        ActivityLog::record('updated', 'Updated site settings');

        return back()->with('success', 'Settings saved.');
    }

    protected function groupFor(string $key): string
    {
        return match (true) {
            str_starts_with($key, 'site_'), $key === 'logo_path', $key === 'favicon_path' => 'identity',
            str_starts_with($key, 'meta_'), $key === 'og_image' => 'seo',
            str_starts_with($key, 'google_') => 'analytics',
            str_starts_with($key, 'adsense_'), $key === 'ads_txt_content' => 'ads',
            default => 'general',
        };
    }
}
