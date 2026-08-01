<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Tool;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = collect();

        $urls->push(['loc' => canonical_url('/'), 'lastmod' => now()]);
        $urls->push(['loc' => canonical_url('/tools/'), 'lastmod' => now()]);
        $urls->push(['loc' => canonical_url('/blog/'), 'lastmod' => now()]);
        $urls->push(['loc' => canonical_url('/news/'), 'lastmod' => now()]);

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

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
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
