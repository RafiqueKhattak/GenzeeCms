<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects extension-less GET paths without a trailing slash to the
 * slash-terminated canonical URL, matching the legacy static site's
 * folder-style URLs (e.g. /tools/age-calculator -> /tools/age-calculator/).
 */
class ForceTrailingSlash
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        if (
            in_array($request->method(), ['GET', 'HEAD'], true)
            && $path !== '/'
            && ! str_ends_with($path, '/')
            && ! str_contains(basename($path), '.')
            && ! $request->ajax()
            && ! $request->header('X-Inertia')
        ) {
            // redirect()->to() strips trailing slashes via UrlGenerator::to(), so
            // build the Location header directly via away() to preserve it.
            $target = $path.'/'.($request->getQueryString() ? '?'.$request->getQueryString() : '');

            return redirect()->away($target, 301);
        }

        return $next($request);
    }
}
