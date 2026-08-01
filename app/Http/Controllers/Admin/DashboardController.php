<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Post;
use App\Models\Tool;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'tools' => Tool::count(),
                'toolsPublished' => Tool::where('status', 'published')->count(),
                'blogPosts' => Post::where('type', 'blog')->count(),
                'newsPosts' => Post::where('type', 'news')->count(),
                'drafts' => Post::where('status', 'draft')->count(),
                'scheduled' => Post::where('status', 'scheduled')->count(),
                'users' => User::count(),
            ],
            'recentActivity' => ActivityLog::with('user:id,name')
                ->latest()
                ->take(10)
                ->get(),
            'recentPosts' => Post::latest('updated_at')
                ->take(5)
                ->get(['id', 'title', 'type', 'status', 'updated_at']),
        ]);
    }
}
