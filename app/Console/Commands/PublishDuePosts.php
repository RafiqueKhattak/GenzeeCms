<?php

namespace App\Console\Commands;

use App\Http\Controllers\Site\PostController as PublicPostController;
use App\Http\Controllers\Site\SeoController;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * The public site already treats a scheduled post as live once published_at
 * passes (Post::scopePublished() checks the date, not just the status
 * column) — but nothing flips the `status` column itself from 'scheduled'
 * to 'published', so the admin Posts list keeps showing it as "scheduled"
 * forever after the fact. This command just corrects that label, and busts
 * the public listing/sitemap caches so the newly-live post shows up
 * immediately rather than waiting out their TTL.
 */
class PublishDuePosts extends Command
{
    protected $signature = 'posts:publish-due';

    protected $description = "Flip status scheduled -> published for posts whose published_at has passed";

    public function handle(): int
    {
        $due = Post::where('status', 'scheduled')
            ->where('published_at', '<=', now())
            ->get(['id', 'title']);

        if ($due->isEmpty()) {
            $this->info('No due posts.');

            return self::SUCCESS;
        }

        Post::whereIn('id', $due->pluck('id'))->update(['status' => 'published']);

        Cache::forget(SeoController::CACHE_KEY);
        Cache::forget(PublicPostController::INDEX_CACHE_KEY_PREFIX.'blog');
        Cache::forget(PublicPostController::INDEX_CACHE_KEY_PREFIX.'news');

        foreach ($due as $post) {
            $this->info("Published: {$post->title}");
        }

        return self::SUCCESS;
    }
}
