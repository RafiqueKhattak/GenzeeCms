<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records a row in `page_views` for each successful public page request.
 *
 * Writes happen in terminate(), after the response has been sent, so the
 * visitor never waits on the insert. No IP address or per-visitor identifier
 * is stored — only a country code lifted from a CDN/proxy header (see
 * countryFor()), which keeps this out of personal-data territory.
 */
class RecordPageView
{
    /** Never recorded — admin, auth, assets, and machine-readable endpoints. */
    protected const IGNORED_PREFIXES = [
        'admin', 'login', 'logout', 'register', 'profile', 'password', 'forgot-password',
        'reset-password', 'confirm-password', 'verify-email', 'email', 'two-factor-challenge',
        'build', 'storage', 'assets', 'up',
    ];

    protected const IGNORED_PATHS = ['/sitemap.xml', '/robots.txt', '/ads.txt', '/favicon.ico'];

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! $this->shouldRecord($request, $response)) {
            return;
        }

        try {
            $path = '/'.trim($request->getPathInfo(), '/').($request->getPathInfo() === '/' ? '' : '/');

            PageView::create([
                'path' => $path === '//' ? '/' : $path,
                'subject_type' => $this->subjectTypeFor($request),
                'country_code' => $this->countryFor($request),
                'referrer_host' => $this->referrerHostFor($request),
                'is_bot' => $this->looksLikeBot($request),
                'viewed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Analytics must never break or slow a page. Swallow and log.
            Log::warning('Failed to record page view: '.$e->getMessage());
        }
    }

    protected function shouldRecord(Request $request, Response $response): bool
    {
        if ($request->method() !== 'GET' || $response->getStatusCode() !== 200) {
            return false;
        }

        if ($request->ajax() || $request->header('X-Inertia')) {
            return false;
        }

        $path = $request->getPathInfo();

        if (in_array($path, self::IGNORED_PATHS, true)) {
            return false;
        }

        $firstSegment = explode('/', trim($path, '/'))[0] ?? '';

        return ! in_array($firstSegment, self::IGNORED_PREFIXES, true);
    }

    protected function subjectTypeFor(Request $request): string
    {
        $segments = explode('/', trim($request->getPathInfo(), '/'));
        $first = $segments[0] ?? '';

        if ($first === '') {
            return 'home';
        }

        // A second segment means it's an individual item, not the index page.
        $isDetail = isset($segments[1]) && $segments[1] !== '';

        return match ($first) {
            'tools' => $isDetail ? 'tool' : 'tools-index',
            'blog' => $isDetail ? 'blog' : 'blog-index',
            'news' => $isDetail ? 'news' : 'news-index',
            default => 'page',
        };
    }

    /**
     * Country comes from whatever CDN/proxy sits in front of the app —
     * Cloudflare, CloudFront and most CDNs set one of these. When the app is
     * hit directly (as it is on the current nginx-only setup) there is no
     * header and this returns null, showing as "Unknown" in the admin report.
     * See deploy/README.md for how to populate it.
     */
    protected function countryFor(Request $request): ?string
    {
        foreach (['CF-IPCountry', 'CloudFront-Viewer-Country', 'X-Geo-Country', 'X-Country-Code'] as $header) {
            $value = $request->header($header);
            if ($value && strlen($value) === 2 && ctype_alpha($value) && strtoupper($value) !== 'XX') {
                return strtoupper($value);
            }
        }

        return null;
    }

    protected function referrerHostFor(Request $request): ?string
    {
        $referrer = $request->header('referer');
        if (! $referrer) {
            return null;
        }

        $host = parse_url($referrer, PHP_URL_HOST);
        if (! $host) {
            return null;
        }

        // Internal navigation isn't a useful "traffic source".
        $ownHost = parse_url(config('app.url'), PHP_URL_HOST);
        if ($host === $ownHost || $host === $request->getHost()) {
            return null;
        }

        return substr($host, 0, 255);
    }

    protected function looksLikeBot(Request $request): bool
    {
        $agent = strtolower((string) $request->userAgent());

        if ($agent === '') {
            return true;
        }

        foreach (['bot', 'crawler', 'spider', 'slurp', 'curl', 'wget', 'headless', 'python-requests', 'monitor', 'preview', 'lighthouse'] as $needle) {
            if (str_contains($agent, $needle)) {
                return true;
            }
        }

        return false;
    }
}
