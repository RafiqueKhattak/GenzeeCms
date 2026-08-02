<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KeywordSuggestionController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PolicyCheckController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ToolController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('tools/trash', [ToolController::class, 'trash'])->name('tools.trash');
    Route::post('tools/bulk-action', [ToolController::class, 'bulk'])->name('tools.bulk');
    Route::post('tools/{id}/restore', [ToolController::class, 'restore'])->name('tools.restore');
    Route::delete('tools/{id}/force-delete', [ToolController::class, 'forceDelete'])->name('tools.force-delete');
    Route::resource('tools', ToolController::class)->except('show');

    Route::get('posts/trash', [PostController::class, 'trash'])->name('posts.trash');
    Route::post('posts/bulk-action', [PostController::class, 'bulk'])->name('posts.bulk');
    Route::post('posts/{id}/restore', [PostController::class, 'restore'])->name('posts.restore');
    Route::delete('posts/{id}/force-delete', [PostController::class, 'forceDelete'])->name('posts.force-delete');
    Route::resource('posts', PostController::class)->except('show');

    Route::get('pages', [PageController::class, 'index'])->name('pages.index');
    Route::get('pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
    Route::put('pages/{page}', [PageController::class, 'update'])->name('pages.update');

    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');

    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::post('media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

    Route::get('activity', [ActivityController::class, 'index'])->name('activity.index');

    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    Route::get('keywords', [KeywordSuggestionController::class, 'index'])->name('keywords.index');
    Route::post('keywords', [KeywordSuggestionController::class, 'store'])->name('keywords.store');
    Route::post('keywords/fetch', [KeywordSuggestionController::class, 'fetch'])->name('keywords.fetch');
    Route::put('keywords/{keyword}', [KeywordSuggestionController::class, 'update'])->name('keywords.update');
    Route::delete('keywords/{keyword}', [KeywordSuggestionController::class, 'destroy'])->name('keywords.destroy');

    Route::post('policy-check', [PolicyCheckController::class, 'check'])->name('policy-check');

    Route::get('redirects', [RedirectController::class, 'index'])->name('redirects.index');
    Route::post('redirects', [RedirectController::class, 'store'])->name('redirects.store');
    Route::delete('redirects/{redirect}', [RedirectController::class, 'destroy'])->name('redirects.destroy');

    Route::middleware('admin.role')->group(function () {
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('users/{user}/send-reset-link', [UserController::class, 'sendResetLink'])->name('users.send-reset-link');
        Route::resource('users', UserController::class)->except('show');
    });
});
