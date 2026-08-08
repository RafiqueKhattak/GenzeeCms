<?php

namespace App\Services\Keywords;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pulls recent business/technology headlines from BBC News's official public
 * RSS feeds as raw material for keyword suggestions.
 *
 * An earlier candidate, the unofficial bbc-news-api.vercel.app wrapper, was
 * evaluated and rejected: live testing showed its "Business" and
 * "Technology" sections both returned the same generic top-of-homepage
 * stories (not actually scoped to either topic), which would have fed
 * mislabeled, off-topic text into KeywordRelevance. BBC's own RSS feeds are
 * correctly scoped and need no API key, unlike NewsApiClient.
 */
class BbcRssClient
{
    protected const FEEDS = [
        'https://feeds.bbci.co.uk/news/business/rss.xml',
        'https://feeds.bbci.co.uk/news/technology/rss.xml',
    ];

    public function isConfigured(): bool
    {
        return true;
    }

    /**
     * @return array<int, array{title: string, description: ?string, url: string, source: ?string}>
     */
    public function recentHeadlines(): array
    {
        $articles = [];

        foreach (self::FEEDS as $feed) {
            $articles = array_merge($articles, $this->fetchFeed($feed));
        }

        return $articles;
    }

    protected function fetchFeed(string $feedUrl): array
    {
        try {
            $response = Http::timeout(10)->get($feedUrl);

            if (! $response->successful()) {
                Log::warning('BBC RSS request failed', ['feed' => $feedUrl, 'status' => $response->status()]);

                return [];
            }

            $xml = @simplexml_load_string($response->body());

            if ($xml === false || ! isset($xml->channel->item)) {
                Log::warning('BBC RSS feed did not parse as XML', ['feed' => $feedUrl]);

                return [];
            }

            $articles = [];

            foreach ($xml->channel->item as $item) {
                $title = trim((string) $item->title);

                if ($title === '') {
                    continue;
                }

                $articles[] = [
                    'title' => $title,
                    'description' => trim((string) $item->description) ?: null,
                    'url' => trim((string) $item->link),
                    'source' => 'BBC News',
                ];
            }

            return $articles;
        } catch (\Throwable $e) {
            Log::warning('BBC RSS request threw: '.$e->getMessage(), ['feed' => $feedUrl]);

            return [];
        }
    }
}
