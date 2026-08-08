<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\KeywordSuggestion;
use App\Services\Keywords\BbcRssClient;
use App\Services\Keywords\KeywordRelevance;
use App\Services\Keywords\NewsApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KeywordSuggestionController extends Controller
{
    /** Below this relevance score a fetched headline isn't worth storing. */
    protected const MIN_RELEVANCE_TO_STORE = 15;

    public function index(Request $request, NewsApiClient $newsApi, BbcRssClient $bbcRss): Response
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
            'bbcRssConfigured' => $bbcRss->isConfigured(),
            'lastFetchedAt' => KeywordSuggestion::whereIn('source', ['news-api', 'bbc-rss'])->max('fetched_at'),
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
     * Pulls fresh headlines from every configured source and keeps only
     * those that score as relevant to this site's subject area — trending
     * feeds are mostly sport, celebrity and breaking local news, none of
     * which belongs here.
     */
    public function fetch(NewsApiClient $newsApi, BbcRssClient $bbcRss, KeywordRelevance $relevance): RedirectResponse
    {
        $sources = [];
        if ($newsApi->isConfigured()) {
            $sources['news-api'] = $newsApi->recentHeadlines();
        }
        if ($bbcRss->isConfigured()) {
            $sources['bbc-rss'] = $bbcRss->recentHeadlines();
        }

        if (empty($sources)) {
            return back()->with('error', 'No NEWS_API_KEY is configured — add a free key from newsapi.org to your .env, or add keywords manually.');
        }

        $totalFetched = 0;
        $totalStored = 0;

        foreach ($sources as $source => $headlines) {
            $totalFetched += count($headlines);
            $totalStored += $this->storeHeadlines($headlines, $source, $relevance);
        }

        if ($totalFetched === 0) {
            return back()->with('error', 'The news sources returned nothing. Try again shortly.');
        }

        $skipped = $totalFetched - $totalStored;
        ActivityLog::record('updated', "Fetched keyword suggestions: {$totalStored} relevant, {$skipped} off-topic skipped");

        return back()->with('success', "Fetched {$totalStored} relevant suggestions ({$skipped} off-topic headlines skipped).");
    }

    /** @param array<int, array{title: string, description: ?string, url: string, source: ?string}> $headlines */
    protected function storeHeadlines(array $headlines, string $source, KeywordRelevance $relevance): int
    {
        $stored = 0;

        foreach ($headlines as $headline) {
            $text = $headline['title'].' '.($headline['description'] ?? '');
            $score = $relevance->score($text);

            if ($score < self::MIN_RELEVANCE_TO_STORE) {
                continue;
            }

            $existing = KeywordSuggestion::where('keyword', $headline['title'])
                ->where('source', $source)
                ->first();

            // Don't resurrect something already handled or explicitly dismissed.
            if ($existing && $existing->status !== 'new') {
                continue;
            }

            KeywordSuggestion::updateOrCreate(
                ['keyword' => $headline['title'], 'source' => $source],
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

        return $stored;
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

    /**
     * CSV export (opens directly in Excel/Sheets, no new composer dependency
     * for a true .xlsx writer) of the current status filter's full list —
     * not just the current page — so the site owner can review/plan the
     * whole queue offline before deciding what to turn into posts.
     */
    public function export(Request $request): StreamedResponse
    {
        $status = $request->input('status', 'new');
        $status = in_array($status, ['new', 'used', 'dismissed'], true) ? $status : 'new';

        $suggestions = KeywordSuggestion::with('usedPost:id,title')
            ->where('status', $status)
            ->orderByDesc('relevance')
            ->orderByDesc('created_at')
            ->get();

        $filename = "keyword-ideas-{$status}-".now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($suggestions) {
            $out = fopen('php://output', 'w');
            // BOM so Excel opens the UTF-8 file without mangling non-ASCII keywords.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Keyword / Headline', 'Fit score', 'Suggested type', 'Source', 'Source URL', 'Context', 'Status', 'Used in post', 'Fetched at']);

            foreach ($suggestions as $s) {
                fputcsv($out, [
                    $s->keyword,
                    $s->relevance,
                    $s->suggested_type,
                    $s->source,
                    $s->source_url,
                    $s->context,
                    $s->status,
                    $s->usedPost?->title,
                    optional($s->fetched_at)->format('Y-m-d H:i'),
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
