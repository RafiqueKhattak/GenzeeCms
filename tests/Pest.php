<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

function actingAsAdmin(): \App\Models\User
{
    $admin = \App\Models\User::factory()->create(['role' => 'admin', 'is_active' => true]);
    test()->actingAs($admin);

    return $admin;
}

/**
 * Laravel's normal test HTTP helpers ($this->get() etc.) run every URI
 * through prepareUrlForRequest(), which trims trailing slashes — so
 * $this->get('/old-page/') actually requests '/old-page'. That's exactly
 * wrong for testing this app's trailing-slash legacy routes and the
 * from_path-based Redirect/410 lookup, both of which are trailing-slash
 * sensitive by design. This dispatches a request directly through the
 * kernel, bypassing that normalization, so the exact path is preserved.
 */
function getRaw(string $uri): \Illuminate\Testing\TestResponse
{
    $symfonyRequest = \Symfony\Component\HttpFoundation\Request::create($uri, 'GET');
    $request = \Illuminate\Http\Request::createFromBase($symfonyRequest);
    $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);

    return \Illuminate\Testing\TestResponse::fromBaseResponse($response);
}
