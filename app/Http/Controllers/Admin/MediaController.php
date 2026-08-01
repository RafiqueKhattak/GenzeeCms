<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $media = Media::create([
            'disk' => 'public',
            'path' => $path,
            'type' => $type,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
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
        \Illuminate\Support\Facades\Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        ActivityLog::record('deleted', 'Deleted a media file');

        return back()->with('success', 'File deleted.');
    }
}
