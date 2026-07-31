<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function show(string $slug): Response
    {
        $page = Page::query()->where('slug', $slug)->firstOrFail();

        return Inertia::render('Public/StaticPage', [
            'page' => $page,
            'canonical' => canonical_url("/{$slug}/"),
        ]);
    }
}
