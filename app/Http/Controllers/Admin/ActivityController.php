<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Activity/Index', [
            'logs' => ActivityLog::with('user:id,name')->latest()->paginate(30),
        ]);
    }
}
