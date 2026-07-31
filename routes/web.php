<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\PostController;
use App\Http\Controllers\Site\ToolController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/tools/', [ToolController::class, 'index'])->name('tools.index');
Route::get('/tools/{slug}/', [ToolController::class, 'show'])->name('tools.show');

Route::get('/blog/', [PostController::class, 'blogIndex'])->name('blog.index');
Route::get('/blog/{slug}/', [PostController::class, 'blogShow'])->name('blog.show');

Route::get('/news/', [PostController::class, 'newsIndex'])->name('news.index');
Route::get('/news/{slug}/', [PostController::class, 'newsShow'])->name('news.show');

Route::get('/{slug}/', [PageController::class, 'show'])
    ->whereIn('slug', ['about', 'contact', 'privacy-policy', 'disclaimer', 'terms', 'editorial'])
    ->name('page.show');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
