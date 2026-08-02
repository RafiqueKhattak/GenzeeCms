<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /** Public so Admin\PostController can bust the relevant type's list on save. */
    public const INDEX_CACHE_KEY_PREFIX = 'public.posts.index.';

    public function blogIndex(): Response
    {
        return $this->index('blog', 'Blog');
    }

    public function newsIndex(): Response
    {
        return $this->index('news', 'News');
    }

    public function blogShow(string $slug): Response
    {
        return $this->show('blog', $slug);
    }

    public function newsShow(string $slug): Response
    {
        return $this->show('news', $slug);
    }

    protected function index(string $type, string $label): Response
    {
        $posts = Cache::remember(self::INDEX_CACHE_KEY_PREFIX.$type, now()->addMinutes(10), function () use ($type) {
            return Post::query()
                ->where('type', $type)
                ->published()
                ->orderByDesc('published_at')
                ->get(['title', 'slug', 'excerpt', 'published_at']);
        });

        return Inertia::render("Public/{$label}/Index", [
            'posts' => $posts,
            'canonical' => canonical_url("/{$type}/"),
        ]);
    }

    protected function show(string $type, string $slug): Response
    {
        $post = Post::query()
            ->where('type', $type)
            ->where('slug', $slug)
            ->published()
            ->with('tags')
            ->firstOrFail();

        $post->increment('views');

        $related = Post::query()
            ->where('type', $type)
            ->where('id', '!=', $post->id)
            ->published()
            ->when($post->category_id, fn ($q) => $q->where('category_id', $post->category_id))
            ->orderByDesc('published_at')
            ->take(4)
            ->get(['title', 'slug', 'excerpt', 'published_at']);

        $label = $type === 'blog' ? 'Blog' : 'News';

        return Inertia::render("Public/{$label}/Show", [
            'post' => $post,
            'related' => $related,
            'canonical' => $post->canonical_override ?: canonical_url("/{$type}/{$post->slug}/"),
        ]);
    }
}
