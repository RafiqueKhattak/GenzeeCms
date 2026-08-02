<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\PostController;
use App\Http\Controllers\Site\SeoController;
use App\Http\Controllers\Site\ToolController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/ads.txt', [SeoController::class, 'ads'])->name('ads');

Route::get('/tools/', [ToolController::class, 'index'])->name('tools.index');
Route::get('/tools/{slug}/', [ToolController::class, 'show'])->name('tools.show');

Route::get('/blog/', [PostController::class, 'blogIndex'])->name('blog.index');
Route::get('/blog/{slug}/', [PostController::class, 'blogShow'])->name('blog.show');

Route::get('/news/', [PostController::class, 'newsIndex'])->name('news.index');
Route::get('/news/{slug}/', [PostController::class, 'newsShow'])->name('news.show');

Route::get('/{slug}/', [PageController::class, 'show'])
    ->whereIn('slug', ['about', 'contact', 'privacy-policy', 'disclaimer', 'terms', 'editorial'])
    ->name('page.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/profile/two-factor', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/profile/two-factor', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::delete('/profile/two-factor', [TwoFactorController::class, 'destroy'])->name('two-factor.disable');
    Route::post('/profile/two-factor/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('two-factor.recovery-codes');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
