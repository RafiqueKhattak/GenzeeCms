<?php

use App\Models\Post;
use App\Services\PolicyChecker\DuplicateContentChecker;
use App\Support\ContentSimilarity;

function longBody(string $sentence): string
{
    return '<p>'.str_repeat($sentence.' ', 40).'</p>';
}

test('identical text scores 1.0 and unrelated text scores near zero', function () {
    $a = longBody('The compound interest formula multiplies the principal by the growth rate each period.');
    $b = longBody('Zakat is calculated at two and a half percent of qualifying wealth held for a lunar year.');

    expect(ContentSimilarity::score($a, $a))->toBe(1.0)
        ->and(ContentSimilarity::score($a, $b))->toBeLessThan(0.05);
});

test('an exact copy of an existing post is flagged as a failure', function () {
    $existing = Post::create([
        'type' => 'blog', 'slug' => 'original-post', 'title' => 'Original Post',
        'body' => longBody('Interest compounds when earnings are reinvested rather than withdrawn each period.'),
        'status' => 'published',
    ]);

    $finding = (new DuplicateContentChecker)->check('Copied Post', $existing->body);

    expect($finding->severity)->toBe('fail')
        ->and($finding->message)->toContain('Original Post');
});

test('editing a post does not flag it as a duplicate of itself', function () {
    $post = Post::create([
        'type' => 'blog', 'slug' => 'self-post', 'title' => 'Self Post',
        'body' => longBody('Interest compounds when earnings are reinvested rather than withdrawn each period.'),
        'status' => 'published',
    ]);

    $finding = (new DuplicateContentChecker)->check($post->title, $post->body, $post->id);

    expect($finding->severity)->toBe('pass');
});

test('genuinely original content passes', function () {
    Post::create([
        'type' => 'blog', 'slug' => 'existing', 'title' => 'Existing',
        'body' => longBody('Interest compounds when earnings are reinvested rather than withdrawn each period.'),
        'status' => 'published',
    ]);

    $finding = (new DuplicateContentChecker)->check(
        'Something Else',
        longBody('Land area in Pakistan is measured in marla and kanal, which vary by region.')
    );

    expect($finding->severity)->toBe('pass');
});

test('the policy-check endpoint includes the duplicate finding and downgrades the status', function () {
    actingAsAdmin();

    $existing = Post::create([
        'type' => 'blog', 'slug' => 'source-post', 'title' => 'Source Post',
        'body' => longBody('Interest compounds when earnings are reinvested rather than withdrawn each period.'),
        'status' => 'published',
    ]);

    $response = $this->postJson(route('admin.policy-check'), [
        'type' => 'blog',
        'title' => 'A Perfectly Reasonable Title',
        'body' => $existing->body,
        'meta_description' => 'A short and valid meta description for this post.',
        'featured_image' => '/storage/media/x.jpg',
        'category_id' => null,
        'tags' => ['finance'],
    ]);

    $response->assertOk();

    $findings = collect($response->json('findings'));
    $duplicate = $findings->firstWhere('key', 'duplicate_content');

    expect($duplicate)->not->toBeNull()
        ->and($duplicate['severity'])->toBe('fail')
        ->and($response->json('status'))->toBe('not_approvable');
});

test('the policy-check endpoint passes clean original content', function () {
    actingAsAdmin();

    // Varied sentences, not one line repeated — a repeated sentence would
    // (correctly) trip the checker's own repetition/originality rule and
    // muddy what this test is actually asserting.
    $body = '<h2>Measuring land in Pakistan</h2><p>'
        .collect(range(1, 45))
        ->map(fn ($i) => "Plot number {$i} was recorded in the local revenue register with its own distinct boundary description and measurement notes taken during the survey.")
        ->implode(' ')
        .'</p>';

    $response = $this->postJson(route('admin.policy-check'), [
        'type' => 'blog',
        'title' => 'A Perfectly Reasonable Title',
        'body' => $body,
        'meta_description' => 'A short and valid meta description for this post.',
        'featured_image' => '/storage/media/x.jpg',
        'category_id' => null,
        'tags' => ['property'],
    ]);

    $response->assertOk();

    expect(collect($response->json('findings'))->firstWhere('key', 'duplicate_content')['severity'])->toBe('pass')
        ->and($response->json('status'))->toBe('approvable');
});
