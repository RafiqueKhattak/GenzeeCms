<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::orderBy('name')->get(['id', 'name', 'email', 'role', 'is_active', 'created_at']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Users/Form', ['user' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'editor'])],
            'password' => ['required', Password::defaults()],
        ]);

        $user = User::create([
            ...$data,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        ActivityLog::record('created', "Created user \"{$user->name}\"", $user);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Admin/Users/Form', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'editor'])],
            'is_active' => ['boolean'],
            'password' => ['nullable', Password::defaults()],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        ActivityLog::record('updated', "Updated user \"{$user->name}\"", $user);

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::record('deleted', "Deleted user \"{$name}\"");

        return back()->with('success', 'User deleted.');
    }
}
