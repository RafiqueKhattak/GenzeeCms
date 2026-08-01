<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RedirectController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Redirects/Index', [
            'redirects' => Redirect::orderByDesc('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'from_path' => ['required', 'string', 'max:500', 'unique:redirects,from_path'],
            'to_path' => ['required', 'string', 'max:500'],
            'status_code' => ['required', Rule::in([301, 302])],
        ]);

        $data['from_path'] = '/'.ltrim($data['from_path'], '/');
        $data['to_path'] = '/'.ltrim($data['to_path'], '/');

        Redirect::create($data);

        ActivityLog::record('created', "Created redirect {$data['from_path']} \u{2192} {$data['to_path']}");

        return back()->with('success', 'Redirect created.');
    }

    public function destroy(Redirect $redirect): RedirectResponse
    {
        $redirect->delete();

        ActivityLog::record('deleted', "Deleted redirect {$redirect->from_path}");

        return back()->with('success', 'Redirect deleted.');
    }
}
