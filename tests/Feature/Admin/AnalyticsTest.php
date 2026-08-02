<?php

use App\Models\PageView;
use App\Models\Tool;

test('the analytics page renders and resolves page titles from paths', function () {
    actingAsAdmin();

    Tool::create([
        'slug' => 'demo-calculator', 'title' => 'Demo Calculator', 'component' => 'X',
        'status' => 'published', 'keywords' => [],
    ]);

    PageView::create([
        'path' => '/tools/demo-calculator/', 'subject_type' => 'tool',
        'country_code' => 'PK', 'is_bot' => false, 'viewed_at' => now(),
    ]);
    PageView::create([
        'path' => '/tools/demo-calculator/', 'subject_type' => 'tool',
        'country_code' => 'GB', 'is_bot' => false, 'viewed_at' => now(),
    ]);
    PageView::create([
        'path' => '/blog/', 'subject_type' => 'blog-index',
        'is_bot' => true, 'viewed_at' => now(),
    ]);

    $response = $this->get(route('admin.analytics.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Analytics/Index')
            ->where('summary.totalViews', 2)      // bot row excluded by default
            ->where('summary.botViews', 1)
            ->where('summary.countriesSeen', 2)
            ->where('topPages.0.title', 'Demo Calculator')
            ->where('topPages.0.views', 2)
        );
});

test('bot views are included when the bots filter is on', function () {
    actingAsAdmin();

    PageView::create(['path' => '/', 'subject_type' => 'home', 'is_bot' => true, 'viewed_at' => now()]);
    PageView::create(['path' => '/', 'subject_type' => 'home', 'is_bot' => false, 'viewed_at' => now()]);

    $this->get(route('admin.analytics.index', ['bots' => 1]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('summary.totalViews', 2));
});

test('views outside the selected window are excluded', function () {
    actingAsAdmin();

    PageView::create(['path' => '/', 'subject_type' => 'home', 'is_bot' => false, 'viewed_at' => now()->subDays(45)]);
    PageView::create(['path' => '/', 'subject_type' => 'home', 'is_bot' => false, 'viewed_at' => now()]);

    $this->get(route('admin.analytics.index', ['days' => 30]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('summary.totalViews', 1));

    $this->get(route('admin.analytics.index', ['days' => 90]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('summary.totalViews', 2));
});

test('the page view recorder ignores admin paths and flags bots', function () {
    // getRaw() preserves the trailing slash — the normal test helpers strip it,
    // which would just hit ForceTrailingSlash's 301 instead of the real page.
    getRaw('/tools/', ['HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh) AppleWebKit/537.36 Chrome/120 Safari/537.36'])
        ->assertOk();

    expect(PageView::where('path', '/tools/')->where('is_bot', false)->count())->toBe(1);

    getRaw('/tools/', ['HTTP_USER_AGENT' => 'Googlebot/2.1 (+http://www.google.com/bot.html)']);
    expect(PageView::where('path', '/tools/')->where('is_bot', true)->count())->toBe(1);

    // Admin path: never recorded, even though it redirects to login.
    getRaw('/admin');
    expect(PageView::where('path', 'like', '%admin%')->count())->toBe(0);

    // Machine-readable endpoints are excluded too.
    getRaw('/sitemap.xml');
    expect(PageView::where('path', 'like', '%sitemap%')->count())->toBe(0);
});
