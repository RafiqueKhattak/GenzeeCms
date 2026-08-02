<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Support\HtmlSanitizer;
use App\Http\Controllers\Site\SeoController;
use App\Http\Controllers\Site\PostController as PublicPostController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        Cache::forget(SeoController::CACHE_KEY);
        Cache::forget(PublicPostController::INDEX_CACHE_KEY_PREFIX.'blog');
        Cache::forget(PublicPostController::INDEX_CACHE_KEY_PREFIX.'news');

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
        Cache::forget(SeoController::CACHE_KEY);
        Cache::forget(PublicPostController::INDEX_CACHE_KEY_PREFIX.'blog');
        Cache::forget(PublicPostController::INDEX_CACHE_KEY_PREFIX.'news');

        ActivityLog::record('updated', "Updated {$post->type} post \"{$post->title}\"", $post);

        return redirect()->route('admin.posts.index')->with('success', 'Post updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $title = $post->title;
        // Free up the (type, slug) pair immediately (DB-level unique index
        // doesn't know about deleted_at) so a new post can reuse it right
        // away — mirrors restore()'s slug-recovery logic below.
        $this->trashOne($post);
        Cache::forget(SeoController::CACHE_KEY);
        Cache::forget(PublicPostController::INDEX_CACHE_KEY_PREFIX.'blog');
        Cache::forget(PublicPostController::INDEX_CACHE_KEY_PREFIX.'news');

        ActivityLog::record('deleted', "Moved post \"{$title}\" to trash");

        return back()->with('success', 'Post moved to trash.');
    }

    public function trash(): Response
    {
        $posts = Post::onlyTrashed()
            ->with(['category', 'author:id,name'])
            ->orderByDesc('deleted_at')
            ->paginate(20);

        return Inertia::render('Admin/Posts/Trash', ['posts' => $posts]);
    }

    public function restore(int $id): RedirectResponse
    {
        $post = Post::onlyTrashed()->findOrFail($id);

        $originalSlug = preg_replace('/-deleted-'.$post->id.'$/', '', $post->slug);
        if (
            $originalSlug !== $post->slug
            && ! Post::where('type', $post->type)->where('slug', $originalSlug)->exists()
        ) {
            $post->slug = $originalSlug;
        }

        $post->restore();
        Cache::forget(SeoController::CACHE_KEY);
        Cache::forget(PublicPostController::INDEX_CACHE_KEY_PREFIX.'blog');
        Cache::forget(PublicPostController::INDEX_CACHE_KEY_PREFIX.'news');

        ActivityLog::record('restored', "Restored post \"{$post->title}\"", $post);

        return back()->with('success', 'Post restored.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $post = Post::onlyTrashed()->findOrFail($id);
        $title = $post->title;

        $post->tags()->detach();
        $post->forceDelete();

        ActivityLog::record('deleted', "Permanently deleted post \"{$title}\"");

        return back()->with('success', 'Post permanently deleted.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:posts,id'],
            'action' => ['required', Rule::in(['publish', 'draft', 'delete'])],
        ]);

        $posts = Post::whereIn('id', $data['ids'])->get();

        foreach ($posts as $post) {
            match ($data['action']) {
                'publish' => $post->update(['status' => 'published', 'published_at' => $post->published_at ?? now()]),
                'draft' => $post->update(['status' => 'draft']),
                'delete' => $this->trashOne($post),
            };
        }

        Cache::forget(SeoController::CACHE_KEY);
        Cache::forget(PublicPostController::INDEX_CACHE_KEY_PREFIX.'blog');
        Cache::forget(PublicPostController::INDEX_CACHE_KEY_PREFIX.'news');

        $count = $posts->count();
        ActivityLog::record('updated', "Bulk {$data['action']} on {$count} post(s)");

        return back()->with('success', "Bulk action applied to {$count} post(s).");
    }

    protected function trashOne(Post $post): void
    {
        $post->update(['slug' => $post->slug.'-deleted-'.$post->id]);
        $post->delete();
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

        $data['body'] = HtmlSanitizer::clean($data['body']);

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
