<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tool;
use App\Models\ToolFaq;
use App\Support\HtmlPageParser;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ImportLegacyContent extends Command
{
    protected $signature = 'content:import {path : Absolute path to the genzeetools checkout}';

    protected $description = 'Import tools, blog posts, news items and static pages from the legacy genzeetools static site into the database.';

    public function handle(): int
    {
        $base = rtrim($this->argument('path'), '/');

        if (! is_dir($base)) {
            $this->error("Path not found: {$base}");

            return self::FAILURE;
        }

        $this->importTools($base);
        $this->importPosts($base, 'blog', 'articles.json');
        $this->importPosts($base, 'news', 'news.json');
        $this->importStaticPages($base);

        $this->info('Legacy content import complete.');

        return self::SUCCESS;
    }

    protected function importTools(string $base): void
    {
        $json = json_decode(file_get_contents("{$base}/data/tools.json"), true);

        $categoryOrder = 0;
        $categoryMap = [];
        foreach ($json['categories'] as $cat) {
            $category = Category::updateOrCreate(
                ['type' => 'tool', 'slug' => Str::slug($cat['name'])],
                ['name' => $cat['name'], 'tagline' => $cat['tagline'] ?? null, 'order' => $categoryOrder++]
            );
            $categoryMap[$cat['name']] = $category->id;
        }

        $order = 0;
        $relatedBySlug = [];

        foreach ($json['tools'] as $item) {
            $htmlPath = "{$base}/tools/{$item['slug']}/index.html";
            if (! is_file($htmlPath)) {
                $this->warn("Missing HTML for tool: {$item['slug']}");

                continue;
            }

            $page = HtmlPageParser::fromFile($htmlPath);

            $tool = Tool::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'category_id' => $categoryMap[$item['category']] ?? null,
                    'title' => $item['title'],
                    'icon' => $item['icon'] ?? null,
                    'component' => Str::studly($item['slug']),
                    'short_description' => $page->leadText() ?? $item['desc'] ?? null,
                    'guide_content' => $page->proseDivHtml(),
                    'keywords' => $item['keywords'] ?? [],
                    'meta_title' => $page->title(),
                    'meta_description' => $page->metaContent('description'),
                    'og_image' => $page->metaProperty('og:image'),
                    'status' => 'published',
                    'order' => $order++,
                    'published_at' => now(),
                ]
            );

            foreach ($page->jsonLdBlocks() as $block) {
                if (($block['@type'] ?? null) === 'FAQPage') {
                    $tool->faqs()->delete();
                    $faqOrder = 0;
                    foreach ($block['mainEntity'] ?? [] as $qa) {
                        ToolFaq::create([
                            'tool_id' => $tool->id,
                            'question' => $qa['name'] ?? '',
                            'answer' => $qa['acceptedAnswer']['text'] ?? '',
                            'order' => $faqOrder++,
                        ]);
                    }
                }
            }

            $relatedBySlug[$item['slug']] = $item['related'] ?? [];
        }

        foreach ($relatedBySlug as $slug => $related) {
            $tool = Tool::where('slug', $slug)->first();
            if (! $tool || empty($related)) {
                continue;
            }
            $ids = Tool::whereIn('slug', $related)->pluck('id', 'slug');
            $sync = [];
            foreach ($related as $i => $relSlug) {
                if (isset($ids[$relSlug])) {
                    $sync[$ids[$relSlug]] = ['order' => $i];
                }
            }
            $tool->related()->sync($sync);
        }

        $this->info('Tools imported: '.Tool::count());
    }

    protected function importPosts(string $base, string $type, string $jsonFile): void
    {
        $json = json_decode(file_get_contents("{$base}/data/{$jsonFile}"), true);
        $dir = $type === 'blog' ? 'blog' : 'news';

        foreach ($json as $item) {
            $htmlPath = "{$base}/{$dir}/{$item['slug']}/index.html";
            if (! is_file($htmlPath)) {
                $this->warn("Missing HTML for {$type}: {$item['slug']}");

                continue;
            }

            $page = HtmlPageParser::fromFile($htmlPath);
            $article = $page->jsonLdByType('Article');
            $body = $page->proseArticleHtml() ?? '';
            $image = $page->firstImageSrc();

            Post::updateOrCreate(
                ['type' => $type, 'slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'excerpt' => $item['summary'] ?? $page->metaContent('description'),
                    'body' => $body,
                    'featured_image' => $image,
                    'meta_title' => $page->title(),
                    'meta_description' => $page->metaContent('description'),
                    'og_image' => $page->metaProperty('og:image'),
                    'status' => 'published',
                    'published_at' => Carbon::parse($article['dateModified'] ?? $item['date']),
                ]
            );
        }

        $this->info(ucfirst($type).' posts imported: '.Post::where('type', $type)->count());
    }

    protected function importStaticPages(string $base): void
    {
        $pages = ['about', 'contact', 'privacy-policy', 'disclaimer', 'terms'];

        foreach ($pages as $slug) {
            $htmlPath = "{$base}/{$slug}/index.html";
            if (! is_file($htmlPath)) {
                continue;
            }

            $page = HtmlPageParser::fromFile($htmlPath);

            Page::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $page->title(),
                    'body' => $page->proseArticleHtml() ?? '',
                    'meta_title' => $page->title(),
                    'meta_description' => $page->metaContent('description'),
                ]
            );
        }

        $this->info('Static pages imported: '.Page::count());
    }
}
