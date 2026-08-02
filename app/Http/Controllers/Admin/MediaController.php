<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Tool;
use App\Support\ImageOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Media/Index', [
            'media' => Media::with('uploader:id,name')->latest()->paginate(24)->withQueryString(),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg,mp4,webm,mov', 'max:20480'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('file');
        $type = str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image';
        $path = $file->store('media/'.date('Y/m'), 'public');

        if ($type === 'image') {
            ImageOptimizer::optimize(Storage::disk('public')->path($path), $file->getMimeType());
        }

        $media = Media::create([
            'disk' => 'public',
            'path' => $path,
            'type' => $type,
            'mime_type' => $file->getMimeType(),
            'size' => Storage::disk('public')->size($path),
            'alt_text' => $request->input('alt_text'),
            'uploaded_by' => $request->user()->id,
        ]);

        ActivityLog::record('uploaded', "Uploaded media \"{$file->getClientOriginalName()}\"", $media);

        if ($request->wantsJson()) {
            return response()->json($media);
        }

        return back()->with('success', 'File uploaded.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        $usage = $this->findUsage($media);

        if ($usage->isNotEmpty()) {
            $list = $usage->take(5)->implode(', ');
            $more = $usage->count() > 5 ? ' and '.($usage->count() - 5).' more' : '';

            return back()->with('error', "Can't delete — still referenced by {$list}{$more}. Remove it from there first.");
        }

        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        ActivityLog::record('deleted', 'Deleted a media file');

        return back()->with('success', 'File deleted.');
    }

    /**
     * Media has no foreign keys pointing at it — Tool/Post/Page/Setting all
     * store the `/storage/{path}` URL as a plain string (og_image,
     * featured_image, logo/favicon, or inline in rich-text body/guide
     * content). Deleting a still-referenced file silently breaks a public
     * page's image, so scan the plausible referencing fields first.
     */
    protected function findUsage(Media $media): Collection
    {
        $ref = '/storage/'.$media->path;
        $usage = collect();

        Tool::where('og_image', $ref)
            ->orWhere('guide_content', 'like', "%{$ref}%")
            ->get(['title'])
            ->each(fn (Tool $tool) => $usage->push("tool \"{$tool->title}\""));

        Post::where('featured_image', $ref)
            ->orWhere('og_image', $ref)
            ->orWhere('body', 'like', "%{$ref}%")
            ->get(['title'])
            ->each(fn (Post $post) => $usage->push("post \"{$post->title}\""));

        Page::where('body', 'like', "%{$ref}%")
            ->get(['title'])
            ->each(fn (Page $page) => $usage->push("page \"{$page->title}\""));

        if (Setting::whereIn('key', ['logo_path', 'favicon_path', 'og_image'])->where('value', $ref)->exists()) {
            $usage->push('site settings');
        }

        return $usage;
    }
}
