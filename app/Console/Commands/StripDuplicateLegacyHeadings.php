<?php

namespace App\Console\Commands;

use App\Http\Controllers\Site\PostController as PublicPostController;
use App\Http\Controllers\Site\SeoController;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * One-time cleanup: legacy-imported post bodies already carry their own
 * "<h1>{title}</h1><p class="article-meta">Published ... </p>" from the old
 * static site's HTML. Blog/Show.vue and News/Show.vue render their own H1 +
 * author byline above post.body, so imported posts show the title and date
 * twice. Strips only that exact leading block, once, from the start of the
 * body — leaves everything else (including the real lead paragraph) intact.
 */
class StripDuplicateLegacyHeadings extends Command
{
    protected $signature = 'content:strip-duplicate-headings {--dry-run : List affected posts without saving changes}';

    protected $description = 'Remove the leading <h1>title</h1><p class="article-meta">...</p> block that legacy-imported posts already have baked into their body';

    public function handle(): int
    {
        $pattern = '/^\s*<h1>.*?<\/h1>\s*<p class="article-meta">.*?<\/p>\s*/is';

        $posts = Post::where('body', 'like', '<h1>%')->get(['id', 'title', 'body']);
        $affected = 0;

        foreach ($posts as $post) {
            $newBody = preg_replace($pattern, '', $post->body, 1);

            if ($newBody === $post->body) {
                continue;
            }

            $affected++;
            $this->info("{$post->id}: {$post->title}");

            if (! $this->option('dry-run')) {
                $post->update(['body' => $newBody]);
            }
        }

        if ($affected === 0) {
            $this->info('No posts had a duplicated leading heading.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("Dry run: {$affected} post(s) would be updated. Re-run without --dry-run to apply.");

            return self::SUCCESS;
        }

        Cache::forget(SeoController::CACHE_KEY);
        Cache::forget(PublicPostController::INDEX_CACHE_KEY_PREFIX.'blog');
        Cache::forget(PublicPostController::INDEX_CACHE_KEY_PREFIX.'news');

        $this->info("Updated {$affected} post(s).");

        return self::SUCCESS;
    }
}
