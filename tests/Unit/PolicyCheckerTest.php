<?php

use App\Services\PolicyChecker\PolicyCheckRequest;
use App\Services\PolicyChecker\RuleBasedPolicyChecker;

function goodPolicyRequest(array $overrides = []): PolicyCheckRequest
{
    $longBody = '<h2>Heading</h2><p>'.str_repeat('This is a genuinely useful, original sentence about the topic. ', 60).'</p>';

    // array_key_exists (not ??) so overriding a field to null — to test the
    // "missing" branch of a check — isn't silently replaced by the default.
    $get = fn (string $key, $default) => array_key_exists($key, $overrides) ? $overrides[$key] : $default;

    return new PolicyCheckRequest(
        type: $get('type', 'blog'),
        title: $get('title', 'A Clear, Descriptive Title'),
        bodyHtml: $get('bodyHtml', $longBody),
        excerpt: $get('excerpt', 'A short summary.'),
        metaDescription: $get('metaDescription', 'A meta description under 160 characters that summarises the post nicely.'),
        featuredImage: $get('featuredImage', '/storage/media/2026/01/example.jpg'),
        category: $get('category', 'Finance'),
        tags: $get('tags', ['budgeting']),
    );
}

test('well-formed content scores highly and is approvable', function () {
    $result = (new RuleBasedPolicyChecker)->check(goodPolicyRequest());

    expect($result->status)->toBe('approvable')
        ->and($result->score)->toBeGreaterThanOrEqual(85)
        ->and(collect($result->findings)->pluck('severity'))->not->toContain('fail');
});

test('prohibited content is flagged as a fail and not approvable regardless of score', function () {
    $result = (new RuleBasedPolicyChecker)->check(goodPolicyRequest([
        'bodyHtml' => '<p>Click here to buy cocaine online today.</p>',
    ]));

    expect($result->status)->toBe('not_approvable');
    $keys = collect($result->findings)->pluck('key');
    expect($keys)->toContain('prohibited_content');
});

test('very short content fails the word count check', function () {
    $result = (new RuleBasedPolicyChecker)->check(goodPolicyRequest([
        'bodyHtml' => '<p>Too short.</p>',
    ]));

    expect($result->status)->toBe('not_approvable');
    $wordCountFinding = collect($result->findings)->firstWhere('key', 'word_count');
    expect($wordCountFinding->severity)->toBe('fail');
});

test('missing meta description and featured image produce warnings, not failures', function () {
    $result = (new RuleBasedPolicyChecker)->check(goodPolicyRequest([
        'metaDescription' => null,
        'featuredImage' => null,
    ]));

    $findings = collect($result->findings)->keyBy('key');
    expect($findings['meta_description']->severity)->toBe('warn')
        ->and($findings['featured_image']->severity)->toBe('warn')
        ->and($result->status)->not->toBe('not_approvable');
});

test('placeholder text left in the body is a fail', function () {
    $result = (new RuleBasedPolicyChecker)->check(goodPolicyRequest([
        'bodyHtml' => '<p>'.str_repeat('Real content sentence. ', 40).'</p><p>TODO: finish this section</p>',
    ]));

    $finding = collect($result->findings)->firstWhere('key', 'placeholder_text');
    expect($finding)->not->toBeNull()
        ->and($finding->severity)->toBe('fail')
        ->and($result->status)->toBe('not_approvable');
});

test('a news post with no excerpt gets a summary warning, a blog post does not', function () {
    $news = (new RuleBasedPolicyChecker)->check(goodPolicyRequest(['type' => 'news', 'excerpt' => null]));
    $blog = (new RuleBasedPolicyChecker)->check(goodPolicyRequest(['type' => 'blog', 'excerpt' => null]));

    expect(collect($news->findings)->pluck('key'))->toContain('excerpt')
        ->and(collect($blog->findings)->pluck('key'))->not->toContain('excerpt');
});
