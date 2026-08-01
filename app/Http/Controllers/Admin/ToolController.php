<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Tool;
use App\Models\ToolFaq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        ActivityLog::record('updated', "Updated tool \"{$tool->title}\"", $tool);

        return redirect()->route('admin.tools.index')->with('success', 'Tool updated.');
    }

    public function destroy(Tool $tool): RedirectResponse
    {
        $title = $tool->title;
        $tool->delete();

        ActivityLog::record('deleted', "Deleted tool \"{$title}\"");

        return back()->with('success', 'Tool deleted.');
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

        $data['keywords'] = $data['keywords']
            ? array_values(array_filter(array_map('trim', explode(',', $data['keywords']))))
            : [];

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
