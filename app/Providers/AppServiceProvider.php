<?php

namespace App\Providers;

use App\Services\PolicyChecker\ContentPolicyCheckerInterface;
use App\Services\PolicyChecker\RuleBasedPolicyChecker;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ContentPolicyCheckerInterface::class, match (config('policy-checker.driver')) {
            default => RuleBasedPolicyChecker::class,
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
