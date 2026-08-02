<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Tool;
use App\Models\ToolFaq;
use App\Support\HtmlSanitizer;
use App\Http\Controllers\Site\SeoController;
use App\Http\Controllers\Site\ToolController as PublicToolController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ToolController extends Controller
{
    public function index(Request $request): Response
    {
        $tools = Tool::query()
            ->with('category')
            ->when($request->search, fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->orderBy('order')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Tools/Index', [
            'tools' => $tools,
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Tools/Form', [
            'tool' => null,
            'categories' => Category::where('type', 'tool')->orderBy('order')->get(),
            'allTools' => Tool::orderBy('title')->get(['id', 'title', 'slug']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $tool = Tool::create($data);
        $this->syncFaqs($tool, $request->input('faqs', []));
        $tool->related()->sync(collect($request->input('related', []))->mapWithKeys(fn ($id, $i) => [$id => ['order' => $i]]));
        Cache::forget(SeoController::CACHE_KEY);
        Cache::forget(PublicToolController::INDEX_CACHE_KEY);

        ActivityLog::record('created', "Created tool \"{$tool->title}\"", $tool);

        return redirect()->route('admin.tools.index')->with('success', 'Tool created.');
    }

    public function edit(Tool $tool): Response
    {
        $tool->load('faqs', 'related');

        return Inertia::render('Admin/Tools/Form', [
            'tool' => $tool,
            'categories' => Category::where('type', 'tool')->orderBy('order')->get(),
            'allTools' => Tool::where('id', '!=', $tool->id)->orderBy('title')->get(['id', 'title', 'slug']),
        ]);
    }

    public function update(Request $request, Tool $tool): RedirectResponse
    {
        $data = $this->validated($request, $tool->id);

        $tool->update($data);
        $this->syncFaqs($tool, $request->input('faqs', []));
        $tool->related()->sync(collect($request->input('related', []))->mapWithKeys(fn ($id, $i) => [$id => ['order' => $i]]));
        Cache::forget(SeoController::CACHE_KEY);
        Cache::forget(PublicToolController::INDEX_CACHE_KEY);

        ActivityLog::record('updated', "Updated tool \"{$tool->title}\"", $tool);

        return redirect()->route('admin.tools.index')->with('success', 'Tool updated.');
    }

    public function destroy(Tool $tool): RedirectResponse
    {
        $title = $tool->title;
        // Free up the slug immediately (DB-level unique index doesn't know
        // about deleted_at) so a new tool can reuse it right away, matching
        // the trashed row's own restore() logic below.
        $this->trashOne($tool);
        Cache::forget(SeoController::CACHE_KEY);
        Cache::forget(PublicToolController::INDEX_CACHE_KEY);

        ActivityLog::record('deleted', "Moved tool \"{$title}\" to trash");

        return back()->with('success', 'Tool moved to trash.');
    }

    public function trash(): Response
    {
        $tools = Tool::onlyTrashed()
            ->with('category')
            ->orderByDesc('deleted_at')
            ->paginate(20);

        return Inertia::render('Admin/Tools/Trash', ['tools' => $tools]);
    }

    public function restore(int $id): RedirectResponse
    {
        $tool = Tool::onlyTrashed()->findOrFail($id);

        $originalSlug = preg_replace('/-deleted-'.$tool->id.'$/', '', $tool->slug);
        if ($originalSlug !== $tool->slug && ! Tool::where('slug', $originalSlug)->exists()) {
            $tool->slug = $originalSlug;
        }

        $tool->restore();
        Cache::forget(SeoController::CACHE_KEY);
        Cache::forget(PublicToolController::INDEX_CACHE_KEY);

        ActivityLog::record('restored', "Restored tool \"{$tool->title}\"", $tool);

        return back()->with('success', 'Tool restored.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $tool = Tool::onlyTrashed()->findOrFail($id);
        $title = $tool->title;

        $tool->faqs()->delete();
        $tool->related()->detach();
        $tool->forceDelete();

        ActivityLog::record('deleted', "Permanently deleted tool \"{$title}\"");

        return back()->with('success', 'Tool permanently deleted.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:tools,id'],
            'action' => ['required', Rule::in(['publish', 'draft', 'delete'])],
        ]);

        $tools = Tool::whereIn('id', $data['ids'])->get();

        foreach ($tools as $tool) {
            match ($data['action']) {
                'publish' => $tool->update(['status' => 'published', 'published_at' => $tool->published_at ?? now()]),
                'draft' => $tool->update(['status' => 'draft']),
                'delete' => $this->trashOne($tool),
            };
        }

        Cache::forget(SeoController::CACHE_KEY);
        Cache::forget(PublicToolController::INDEX_CACHE_KEY);

        $count = $tools->count();
        ActivityLog::record('updated', "Bulk {$data['action']} on {$count} tool(s)");

        return back()->with('success', "Bulk action applied to {$count} tool(s).");
    }

    protected function trashOne(Tool $tool): void
    {
        $tool->update(['slug' => $tool->slug.'-deleted-'.$tool->id]);
        $tool->delete();
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'slug' => ['required', 'alpha_dash', Rule::unique('tools', 'slug')->ignore($ignoreId)],
            'title' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:20'],
            'component' => ['required', 'string', 'max:100'],
            'short_description' => ['nullable', 'string'],
            'guide_content' => ['nullable', 'string'],
            'keywords' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'order' => ['nullable', 'integer'],
        ]);

        $data['keywords'] = ($data['keywords'] ?? null)
            ? array_values(array_filter(array_map('trim', explode(',', $data['keywords']))))
            : [];

        $data['guide_content'] = HtmlSanitizer::clean($data['guide_content'] ?? null);

        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function syncFaqs(Tool $tool, array $faqs): void
    {
        $tool->faqs()->delete();
        foreach (array_values($faqs) as $i => $faq) {
            if (empty($faq['question'])) {
                continue;
            }
            ToolFaq::create([
                'tool_id' => $tool->id,
                'question' => $faq['question'],
                'answer' => $faq['answer'] ?? '',
                'order' => $i,
            ]);
        }
    }
}
