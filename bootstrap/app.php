<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\ForceTrailingSlash::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdminAccess::class,
            'admin.role' => \App\Http\Middleware\EnsureAdminRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Covers both "no route matched" and "route matched but the
        // record wasn't found" (e.g. a retired /tools/{slug}/) — Laravel
        // converts both to NotFoundHttpException, so checking the
        // Redirect table here catches every 404 in one place.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
            if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
                return null;
            }

            $redirect = \App\Models\Redirect::query()
                ->where('from_path', '/'.ltrim($request->getPathInfo(), '/'))
                ->first();

            if ($redirect) {
                return redirect()->away($redirect->to_path, $redirect->status_code);
            }

            return null;
        });
    })->create();
