<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\KeywordSuggestion;
use App\Services\Keywords\KeywordRelevance;
use App\Services\Keywords\NewsApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class KeywordSuggestionController extends Controller
{
    /** Below this relevance score a fetched headline isn't worth storing. */
    protected const MIN_RELEVANCE_TO_STORE = 15;

    public function index(Request $request, NewsApiClient $newsApi): Response
    {
        $status = $request->input('status', 'new');
        $status = in_array($status, ['new', 'used', 'dismissed'], true) ? $status : 'new';

        return Inertia::render('Admin/Keywords/Index', [
            'filters' => ['status' => $status],
            'suggestions' => KeywordSuggestion::with('usedPost:id,title,type,slug')
                ->where('status', $status)
                ->orderByDesc('relevance')
                ->orderByDesc('created_at')
                ->paginate(30)
                ->withQueryString(),
            'counts' => [
                'new' => KeywordSuggestion::where('status', 'new')->count(),
                'used' => KeywordSuggestion::where('status', 'used')->count(),
                'dismissed' => KeywordSuggestion::where('status', 'dismissed')->count(),
            ],
            'newsApiConfigured' => $newsApi->isConfigured(),
            'lastFetchedAt' => KeywordSuggestion::where('source', 'news-api')->max('fetched_at'),
        ]);
    }

    public function store(Request $request, KeywordRelevance $relevance): RedirectResponse
    {
        $data = $request->validate([
            'keyword' => ['required', 'string', 'max:255'],
            'suggested_type' => ['required', Rule::in(['blog', 'news', 'tool'])],
            'context' => ['nullable', 'string', 'max:1000'],
        ]);

        KeywordSuggestion::updateOrCreate(
            ['keyword' => $data['keyword'], 'source' => 'manual'],
            [
                'suggested_type' => $data['suggested_type'],
                'context' => $data['context'] ?? null,
                'relevance' => $relevance->score($data['keyword'].' '.($data['context'] ?? '')),
                'status' => 'new',
                'fetched_at' => now(),
            ]
        );

        return back()->with('success', 'Keyword added.');
    }

    /**
     * Pulls fresh headlines and keeps only those that score as relevant to
     * this site's subject area — trending feeds are mostly sport, celebrity
     * and breaking local news, none of which belongs here.
     */
    public function fetch(NewsApiClient $newsApi, KeywordRelevance $relevance): RedirectResponse
    {
        if (! $newsApi->isConfigured()) {
            return back()->with('error', 'No NEWS_API_KEY is configured — add a free key from newsapi.org to your .env, or add keywords manually.');
        }

        $headlines = $newsApi->recentHeadlines();

        if (empty($headlines)) {
            return back()->with('error', 'The news API returned nothing. Check the key and try again shortly.');
        }

        $stored = 0;
        foreach ($headlines as $headline) {
            $text = $headline['title'].' '.($headline['description'] ?? '');
            $score = $relevance->score($text);

            if ($score < self::MIN_RELEVANCE_TO_STORE) {
                continue;
            }

            $existing = KeywordSuggestion::where('keyword', $headline['title'])
                ->where('source', 'news-api')
                ->first();

            // Don't resurrect something already handled or explicitly dismissed.
            if ($existing && $existing->status !== 'new') {
                continue;
            }

            KeywordSuggestion::updateOrCreate(
                ['keyword' => $headline['title'], 'source' => 'news-api'],
                [
                    'source_url' => $headline['url'],
                    'context' => $headline['description'],
                    'suggested_type' => $relevance->suggestType($text),
                    'relevance' => $score,
                    'status' => 'new',
                    'fetched_at' => now(),
                ]
            );
            $stored++;
        }

        $skipped = count($headlines) - $stored;
        ActivityLog::record('updated', "Fetched keyword suggestions: {$stored} relevant, {$skipped} off-topic skipped");

        return back()->with('success', "Fetched {$stored} relevant suggestions ({$skipped} off-topic headlines skipped).");
    }

    public function update(Request $request, KeywordSuggestion $keyword): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'used', 'dismissed'])],
        ]);

        $keyword->update($data);

        return back()->with('success', 'Suggestion updated.');
    }

    public function destroy(KeywordSuggestion $keyword): RedirectResponse
    {
        $keyword->delete();

        return back()->with('success', 'Suggestion removed.');
    }
}
