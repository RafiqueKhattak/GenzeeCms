<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $categories = Category::query()
            ->where('type', 'tool')
            ->orderBy('order')
            ->with(['tools' => fn ($q) => $q->published()->orderBy('order')])
            ->get();

        $latestPosts = Post::query()
            ->blog()
            ->published()
            ->orderByDesc('published_at')
            ->take(6)
            ->get(['title', 'slug']);

        return Inertia::render('Public/Home', [
            'categories' => $categories,
            'latestPosts' => $latestPosts,
            'canonical' => canonical_url('/'),
        ]);
    }
}
