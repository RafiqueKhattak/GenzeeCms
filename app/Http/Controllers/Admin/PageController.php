<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Pages/Index', [
            'pages' => Page::orderBy('title')->get(),
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

        $page->update($data);

        ActivityLog::record('updated', "Updated page \"{$page->title}\"", $page);

        return redirect()->route('admin.pages.index')->with('success', 'Page updated.');
    }
}
