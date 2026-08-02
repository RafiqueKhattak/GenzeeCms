<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
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
        $posts = Cache::remember(self::INDEX_CACHE_KEY_PREFIX.'blog', now()->addMinutes(10), function () {
            return Post::query()
                ->where('type', 'blog')
                ->published()
                ->with('author:id,name,avatar_path')
                ->orderByDesc('published_at')
                ->get(['id', 'title', 'slug', 'excerpt', 'published_at', 'featured_image', 'author_id']);
        });

        return Inertia::render('Public/Blog/Index', [
            'posts' => $posts,
            'canonical' => canonical_url('/blog/'),
        ]);
    }

    public function newsIndex(): Response
    {
        $data = Cache::remember(self::INDEX_CACHE_KEY_PREFIX.'news', now()->addMinutes(10), function () {
            $posts = Post::query()
                ->where('type', 'news')
                ->published()
                ->with('category:id,name,slug')
                ->orderByDesc('published_at')
                ->get(['id', 'title', 'slug', 'published_at', 'category_id', 'views']);

            return [
                'posts' => $posts,
                'departments' => Category::where('type', 'news')->orderBy('order')->get(['id', 'name', 'slug']),
                'ticker' => $posts->sortByDesc('views')->take(12)->values(),
            ];
        });

        return Inertia::render('Public/News/Index', [
            'posts' => $data['posts'],
            'departments' => $data['departments'],
            'ticker' => $data['ticker'],
            'canonical' => canonical_url('/news/'),
        ]);
    }

    public function blogShow(string $slug): Response
    {
        return $this->show('blog', $slug);
    }

    public function newsShow(string $slug): Response
    {
        return $this->show('news', $slug);
    }

    protected function show(string $type, string $slug): Response
    {
        $post = Post::query()
            ->where('type', $type)
            ->where('slug', $slug)
            ->published()
            ->with(['tags', 'category:id,name,slug', 'author:id,name,avatar_path'])
            ->firstOrFail();

        $post->increment('views');

        $related = Post::query()
            ->where('type', $type)
            ->where('id', '!=', $post->id)
            ->published()
            ->when($post->category_id, fn ($q) => $q->where('category_id', $post->category_id))
            ->orderByDesc('published_at')
            ->take(4)
            ->get(['title', 'slug', 'excerpt', 'published_at', 'featured_image']);

        $label = $type === 'blog' ? 'Blog' : 'News';

        return Inertia::render("Public/{$label}/Show", [
            'post' => $post,
            'related' => $related,
            'canonical' => $post->canonical_override ?: canonical_url("/{$type}/{$post->slug}/"),
        ]);
    }
}
