<?php

use App\Models\KeywordSuggestion;
use App\Models\Post;
use App\Services\Keywords\KeywordRelevance;
use Illuminate\Support\Facades\Http;

test('niche keywords score higher than off-topic trending terms', function () {
    $relevance = new KeywordRelevance;

    // Real examples from a Google Trends "trending now" export.
    expect($relevance->score('july tax revenue fbr'))->toBeGreaterThan(40)
        ->and($relevance->score('gen z unemployment study'))->toBeGreaterThan(40)
        ->and($relevance->score('student loan repayment changes'))->toBeGreaterThan(25);

    foreach (['wwe summerslam matches', 'real madrid vs fiorentina', 'sri lanka cricket', 'spokane fires', 'stephen curry'] as $offTopic) {
        expect($relevance->score($offTopic))->toBe(0, "expected '{$offTopic}' to score 0");
    }
});

test('time-sensitive phrasing is suggested as news, evergreen as blog', function () {
    $relevance = new KeywordRelevance;

    expect($relevance->suggestType('FBR announces new filing deadline'))->toBe('news')
        ->and($relevance->suggestType('how compound interest works'))->toBe('blog');
});

test('an admin can add a keyword manually and it is scored', function () {
    actingAsAdmin();

    $this->post(route('admin.keywords.store'), [
        'keyword' => 'income tax calculator pakistan',
        'suggested_type' => 'blog',
    ])->assertRedirect();

    $suggestion = KeywordSuggestion::where('keyword', 'income tax calculator pakistan')->firstOrFail();

    expect($suggestion->source)->toBe('manual')
        ->and($suggestion->status)->toBe('new')
        ->and($suggestion->relevance)->toBeGreaterThan(30);
});

test('the keyword ideas page lists open suggestions with counts', function () {
    actingAsAdmin();

    KeywordSuggestion::create(['keyword' => 'vat rates 2026', 'source' => 'manual', 'relevance' => 40, 'status' => 'new']);
    KeywordSuggestion::create(['keyword' => 'old idea', 'source' => 'manual', 'relevance' => 10, 'status' => 'dismissed']);

    $this->get(route('admin.keywords.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Keywords/Index')
            ->where('counts.new', 1)
            ->where('counts.dismissed', 1)
            ->where('suggestions.data.0.keyword', 'vat rates 2026')
        );
});

test('creating a post from a suggestion prefills the title and marks it used', function () {
    actingAsAdmin();

    $suggestion = KeywordSuggestion::create([
        'keyword' => 'How VAT Works in the UAE',
        'source' => 'manual',
        'suggested_type' => 'blog',
        'relevance' => 45,
        'status' => 'new',
    ]);

    // The editor is prefilled from the suggestion.
    $this->get(route('admin.posts.create', ['keyword_id' => $suggestion->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Posts/Form')
            ->where('fromKeyword.keyword', 'How VAT Works in the UAE')
            ->where('defaultType', 'blog')
        );

    // Saving the post closes the loop back to the suggestion.
    $this->post(route('admin.posts.store'), [
        'type' => 'blog',
        'slug' => 'how-vat-works-in-the-uae',
        'title' => 'How VAT Works in the UAE',
        'body' => '<p>Body copy.</p>',
        'status' => 'draft',
        'keyword_id' => $suggestion->id,
    ])->assertRedirect(route('admin.posts.index'));

    $post = Post::where('slug', 'how-vat-works-in-the-uae')->firstOrFail();

    expect($suggestion->fresh()->status)->toBe('used')
        ->and($suggestion->fresh()->used_post_id)->toBe($post->id);
});

test('fetching without an API key and an empty BBC feed fails gracefully instead of erroring', function () {
    actingAsAdmin();
    config(['services.news_api.key' => null]);
    Http::fake(['feeds.bbci.co.uk/*' => Http::response('', 200)]);

    $this->post(route('admin.keywords.fetch'))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(KeywordSuggestion::count())->toBe(0);
});

test('fetching pulls relevant headlines from the BBC RSS feeds even without a NewsAPI key', function () {
    actingAsAdmin();
    config(['services.news_api.key' => null]);

    $rss = <<<'XML'
    <?xml version="1.0" encoding="UTF-8"?>
    <rss version="2.0"><channel>
        <item>
            <title>State Bank raises interest rates to fight inflation</title>
            <description>The central bank's move affects borrowing costs nationwide.</description>
            <link>https://www.bbc.co.uk/news/articles/example1</link>
        </item>
        <item>
            <title>Match report: local football derby ends in draw</title>
            <description>Neither side could find the net in a goalless first half.</description>
            <link>https://www.bbc.co.uk/news/articles/example2</link>
        </item>
    </channel></rss>
    XML;

    Http::fake(['feeds.bbci.co.uk/*' => Http::response($rss, 200)]);

    $this->post(route('admin.keywords.fetch'))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(KeywordSuggestion::where('source', 'bbc-rss')->where('keyword', 'like', '%interest rates%')->exists())->toBeTrue()
        ->and(KeywordSuggestion::where('source', 'bbc-rss')->where('keyword', 'like', '%football derby%')->exists())->toBeFalse();
});
