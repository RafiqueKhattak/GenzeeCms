<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class ToolController extends Controller
{
    /** Public so Admin\ToolController/CategoryController can bust it immediately on save. */
    public const INDEX_CACHE_KEY = 'public.tools.index';

    public function index(): Response
    {
        $categories = Cache::remember(self::INDEX_CACHE_KEY, now()->addMinutes(10), function () {
            return Category::query()
                ->where('type', 'tool')
                ->orderBy('order')
                ->with(['tools' => fn ($q) => $q->published()->orderBy('order')])
                ->get();
        });

        return Inertia::render('Public/Tools/Index', [
            'categories' => $categories,
            'canonical' => canonical_url('/tools/'),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $tool = Tool::query()
            ->where('slug', $slug)
            ->published()
            ->with(['category', 'faqs', 'related'])
            ->firstOrFail();

        return Inertia::render('Public/Tools/Show', [
            'tool' => $tool,
            'canonical' => canonical_url("/tools/{$tool->slug}/"),
        ]);
    }
}
