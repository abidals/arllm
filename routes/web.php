<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\PostCommentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| الموقع العام
|--------------------------------------------------------------------------
*/

Route::get('/', [SiteController::class, 'index'])->name('home');
Route::get('/search', [SiteController::class, 'search'])->name('site.search');
Route::get('/archives', [SiteController::class, 'archives'])->name('site.archives');
Route::get('section/{category:slug}', [SiteController::class, 'category'])->name('site.category');
Route::get('author/{author}', [SiteController::class, 'author'])->name('site.author');
Route::get('p/{post:slug}', [SiteController::class, 'post'])->name('site.post');

Route::post('p/{post:slug}/comments', [PostCommentController::class, 'store'])
    ->middleware('throttle:comments')
    ->name('site.comments.store');

/*
|--------------------------------------------------------------------------
| الملف الشخصي
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| لوحة التحكم
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'can:access-dashboard', 'throttle:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // المقالات
        Route::get('posts', [PostController::class, 'index'])->name('posts.index');
        Route::get('posts/create', [PostController::class, 'create'])->name('posts.create');
        Route::post('posts', [PostController::class, 'store'])->name('posts.store');
        Route::get('posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
        Route::put('posts/{post}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
        Route::post('posts/{post}/submit-review', [PostController::class, 'submitReview'])->name('posts.review');
        Route::post('posts/{post}/publish', [PostController::class, 'publish'])->name('posts.publish');
        Route::post('posts/{post}/unpublish', [PostController::class, 'unpublish'])->name('posts.unpublish');
        Route::post('posts/import-md', [PostController::class, 'importMarkdown'])->name('posts.import-md');

        // الأقسام
        Route::middleware('can:manage-categories')->group(function () {
            Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        });

        // التعليقات
        Route::get('comments', [CommentController::class, 'index'])->name('comments.index');
        Route::post('comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve');
        Route::post('comments/{comment}/spam', [CommentController::class, 'spam'])->name('comments.spam');
        Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

        // المستخدمون (المدير فقط)
        Route::middleware('can:manage-users')->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        // الإعدادات (المدير فقط)
        Route::middleware('can:manage-settings')->group(function () {
            Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        });
    });

require __DIR__.'/auth.php';