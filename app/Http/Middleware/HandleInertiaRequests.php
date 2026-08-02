<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'site' => [
                'name' => Setting::get('site_name', 'GenzeeLogics'),
                'tagline' => Setting::get('site_tagline'),
                'url' => Setting::get('site_url', config('app.url')),
                'logo' => Setting::get('logo_path', '/assets/img/logo-small.png'),
                'metaTitleSuffix' => Setting::get('meta_title_suffix'),
                'metaDescription' => Setting::get('meta_description'),
                'ogImage' => Setting::get('og_image'),
                'googleAnalyticsId' => Setting::get('google_analytics_id'),
                'googleSiteVerification' => Setting::get('google_site_verification'),
            ],
        ];
    }
}
