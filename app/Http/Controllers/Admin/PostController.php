<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $posts = Post::query()
            ->with(['category', 'author:id,name'])
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Posts/Index', [
            'posts' => $posts,
            'filters' => $request->only('type', 'status', 'search'),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Admin/Posts/Form', [
            'post' => null,
            'defaultType' => $request->query('type', 'blog'),
            'categories' => Category::whereIn('type', ['blog', 'news'])->orderBy('order')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['author_id'] = $request->user()->id;

        $post = Post::create($data);
        $this->syncTags($post, $request->input('tags', []));

        ActivityLog::record('created', "Created {$post->type} post \"{$post->title}\"", $post);

        return redirect()->route('admin.posts.index')->with('success', 'Post created.');
    }

    public function edit(Post $post): Response
    {
        $post->load('tags');

        return Inertia::render('Admin/Posts/Form', [
            'post' => $post,
            'defaultType' => $post->type,
            'categories' => Category::whereIn('type', ['blog', 'news'])->orderBy('order')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $data = $this->validated($request, $post->id);

        $post->update($data);
        $this->syncTags($post, $request->input('tags', []));

        ActivityLog::record('updated', "Updated {$post->type} post \"{$post->title}\"", $post);

        return redirect()->route('admin.posts.index')->with('success', 'Post updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $title = $post->title;
        $post->delete();

        ActivityLog::record('deleted', "Deleted post \"{$title}\"");

        return back()->with('success', 'Post deleted.');
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['blog', 'news'])],
            'category_id' => ['nullable', 'exists:categories,id'],
            'slug' => ['required', 'alpha_dash', Rule::unique('posts', 'slug')->where('type', $request->type)->ignore($ignoreId)],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'body' => ['required', 'string'],
            'featured_image' => ['nullable', 'string', 'max:500'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'canonical_override' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['draft', 'scheduled', 'published'])],
            'published_at' => ['nullable', 'date', Rule::requiredIf($request->status === 'scheduled')],
        ]);

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function syncTags(Post $post, array $tagNames): void
    {
        $ids = collect($tagNames)
            ->filter()
            ->map(function ($name) {
                return Tag::firstOrCreate(
                    ['slug' => \Illuminate\Support\Str::slug($name)],
                    ['name' => $name]
                )->id;
            });

        $post->tags()->sync($ids);
    }
}
