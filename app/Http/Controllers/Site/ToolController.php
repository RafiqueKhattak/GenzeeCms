<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tool;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ToolController extends Controller
{
    public function index(): Response
    {
        $categories = Category::query()
            ->where('type', 'tool')
            ->orderBy('order')
            ->with(['tools' => fn ($q) => $q->published()->orderBy('order')])
            ->get();

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
