<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $published = Post::published()->count();
        $pendingReview = Post::where('status', 'review')->count();
        $scheduled = Post::where('status', 'scheduled')->count();
        $pendingComments = Comment::pending()->count();

        $totalViews = Post::sum('views');

        $latestPosts = Post::with(['author', 'category'])
            ->when(!$user->canPublish(), fn ($q) => $q->where('user_id', $user->id))
            ->latest()
            ->limit(8)
            ->get();

        // أكثر الأقسام إنتاجًا (30 يومًا)
        $categoryStats = Post::published()
            ->where('published_at', '>=', now()->subDays(30))
            ->with('category')
            ->get()
            ->groupBy('category.name')
            ->map->count()
            ->sortDesc()
            ->take(5);

        return view('admin.dashboard', [
            'published' => $published,
            'pendingReview' => $pendingReview,
            'scheduled' => $scheduled,
            'pendingComments' => $pendingComments,
            'totalViews' => $totalViews,
            'latestPosts' => $latestPosts,
            'categoryStats' => $categoryStats,
        ]);
    }

    /**
     * البحث الشامل داخل اللوحة: مقالات بكل الحالات، صفحات، أقسام، مستخدمون، أعداد.
     */
    public function search(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $user = auth()->user();

        $posts = $pages = $categories = $users = $issuePosts = collect();

        if ($q !== '') {
            $q = mb_substr($q, 0, 100);

            $posts = Post::with(['author', 'category'])
                ->when(!$user->canPublish(), fn ($query) => $query->where('user_id', $user->id))
                ->where(function ($query) use ($q) {
                    $query->where('title', 'like', "%{$q}%")
                        ->orWhere('subtitle', 'like', "%{$q}%")
                        ->orWhere('excerpt', 'like', "%{$q}%")
                        ->orWhere('content_md', 'like', "%{$q}%");
                })
                ->latest()
                ->limit(20)
                ->get();

            if ($user->can('manage-pages')) {
                $pages = \App\Models\Page::query()
                    ->where(fn ($query) => $query->where('title', 'like', "%{$q}%")->orWhere('content_md', 'like', "%{$q}%"))
                    ->limit(10)
                    ->get();
            }

            if ($user->can('manage-users')) {
                $users = \App\Models\User::query()
                    ->where(fn ($query) => $query->where('name', 'like', "%{$q}%")->orWhere('display_name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
                    ->limit(10)
                    ->get();
            }

            if ($user->can('manage-categories')) {
                $categories = \App\Models\Category::query()
                    ->where('name', 'like', "%{$q}%")
                    ->limit(10)
                    ->get();
            }

            // بحث في الأعداد (تواريخ بصيغة yyyy-mm-dd أو أع')
            if (preg_match('/^\d{4}-\d{2}(-\d{2})?$/', $q)) {
                if (strlen($q) === 10) {
                    $from = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $q)->startOfDay();
                    $to = (clone $from)->endOfDay();
                } else {
                    $from = \Illuminate\Support\Carbon::createFromFormat('Y-m', $q)->startOfMonth();
                    $to = (clone $from)->endOfMonth();
                }

                $issuePosts = Post::published()
                    ->with(['author', 'category'])
                    ->whereBetween('published_at', [$from, $to])
                    ->orderBy('published_at')
                    ->limit(30)
                    ->get();
            }
        }

        return view('admin.search', [
            'q' => $q,
            'posts' => $posts,
            'pages' => $pages,
            'users' => $users,
            'categories' => $categories,
            'issuePosts' => $issuePosts,
        ]);
    }
}