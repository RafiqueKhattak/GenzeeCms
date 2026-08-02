<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Page;
use App\Models\PageView;
use App\Support\HtmlSanitizer;
use App\Support\SeoScorer;
use App\Http\Controllers\Site\SeoController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function index(): Response
    {
        $pages = Page::orderBy('title')->get();

        $viewCounts = PageView::whereIn('path', $pages->map(fn (Page $p) => "/{$p->slug}/"))
            ->selectRaw('path, count(*) as views')
            ->groupBy('path')
            ->pluck('views', 'path');

        $pages->each(function (Page $page) use ($viewCounts) {
            $page->views = (int) ($viewCounts->get("/{$page->slug}/") ?? 0);
            $page->seo_score = SeoScorer::score($page->meta_title, $page->meta_description, null, $page->body, requireImage: false);
        });

        return Inertia::render('Admin/Pages/Index', [
            'pages' => $pages,
        ]);
    }

    public function edit(Page $page): Response
    {
        return Inertia::render('Admin/Pages/Form', ['page' => $page]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        $data['body'] = HtmlSanitizer::clean($data['body']);

        $page->update($data);
        Cache::forget(SeoController::CACHE_KEY);

        ActivityLog::record('updated', "Updated page \"{$page->title}\"", $page);

        return redirect()->route('admin.pages.index')->with('success', 'Page updated.');
    }
}
