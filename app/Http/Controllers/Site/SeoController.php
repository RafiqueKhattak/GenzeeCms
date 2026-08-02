<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Tool;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    /**
     * Cache key for the rendered sitemap XML. Public so admin controllers
     * that mutate sitemap-eligible content (Tools/Posts/Pages) can bust it
     * immediately on publish rather than waiting out the TTL.
     */
    public const CACHE_KEY = 'sitemap.xml';

    public function sitemap(): Response
    {
        $xml = Cache::remember(self::CACHE_KEY, now()->addHour(), function () {
            return $this->buildSitemap();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    protected function buildSitemap(): string
    {
        $urls = collect();

        $toolsLastmod = Tool::query()->published()->max('updated_at');
        $blogLastmod = Post::query()->blog()->published()->max('updated_at');
        $newsLastmod = Post::query()->news()->published()->max('updated_at');
        $homeLastmod = collect([$toolsLastmod, $blogLastmod, $newsLastmod])->filter()->max() ?? now();

        $urls->push(['loc' => canonical_url('/'), 'lastmod' => $homeLastmod]);
        $urls->push(['loc' => canonical_url('/tools/'), 'lastmod' => $toolsLastmod ?? now()]);
        $urls->push(['loc' => canonical_url('/blog/'), 'lastmod' => $blogLastmod ?? now()]);
        $urls->push(['loc' => canonical_url('/news/'), 'lastmod' => $newsLastmod ?? now()]);

        foreach (['about', 'contact', 'privacy-policy', 'disclaimer', 'terms', 'editorial'] as $slug) {
            $page = Page::query()->where('slug', $slug)->first();
            if ($page) {
                $urls->push(['loc' => canonical_url("/{$slug}/"), 'lastmod' => $page->updated_at]);
            }
        }

        Tool::query()->published()->orderBy('order')->each(function (Tool $tool) use ($urls) {
            $urls->push(['loc' => canonical_url("/tools/{$tool->slug}/"), 'lastmod' => $tool->updated_at]);
        });

        Post::query()->published()->orderByDesc('published_at')->each(function (Post $post) use ($urls) {
            $urls->push(['loc' => canonical_url("/{$post->type}/{$post->slug}/"), 'lastmod' => $post->updated_at]);
        });

        return view('sitemap', ['urls' => $urls])->render();
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin/',
            '',
            'Sitemap: '.canonical_url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines)."\n", 200)->header('Content-Type', 'text/plain');
    }

    public function ads(): Response
    {
        $content = Setting::get('ads_txt_content', '# publisher line will be added after AdSense approval');

        return response(rtrim($content)."\n", 200)->header('Content-Type', 'text/plain');
    }
}
