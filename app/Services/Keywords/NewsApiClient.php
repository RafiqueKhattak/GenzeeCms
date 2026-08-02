<?php

namespace App\Services\Keywords;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pulls recent business/technology headlines from NewsAPI.org's free tier as
 * raw material for keyword suggestions.
 *
 * Requires NEWS_API_KEY in .env — a free key from newsapi.org, which is a
 * plain API key issued on signup, not any kind of account login. When no key
 * is configured every method degrades to an empty result and the admin panel
 * shows manual entry only.
 */
class NewsApiClient
{
    protected const ENDPOINT = 'https://newsapi.org/v2/top-headlines';

    public function isConfigured(): bool
    {
        return filled(config('services.news_api.key'));
    }

    /**
     * @return array<int, array{title: string, description: ?string, url: string, source: ?string}>
     */
    public function recentHeadlines(): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $articles = [];

        // Two categories, since the free tier caps page size and mixing them
        // gives better coverage of the site's money + tech subject areas.
        foreach (['business', 'technology'] as $category) {
            $articles = array_merge($articles, $this->fetchCategory($category));
        }

        return $articles;
    }

    protected function fetchCategory(string $category): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-Api-Key' => config('services.news_api.key')])
                ->get(self::ENDPOINT, [
                    'category' => $category,
                    'language' => 'en',
                    'pageSize' => 50,
                ]);

            if (! $response->successful()) {
                Log::warning('NewsAPI request failed', ['status' => $response->status(), 'body' => $response->body()]);

                return [];
            }

            return collect($response->json('articles', []))
                ->filter(fn ($a) => filled($a['title'] ?? null) && ($a['title'] !== '[Removed]'))
                ->map(fn ($a) => [
                    'title' => $a['title'],
                    'description' => $a['description'] ?? null,
                    'url' => $a['url'] ?? '',
                    'source' => $a['source']['name'] ?? null,
                ])
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::warning('NewsAPI request threw: '.$e->getMessage());

            return [];
        }
    }
}
