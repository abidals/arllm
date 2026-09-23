<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
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

        $dailyCounts = collect(
            Post::published()
                ->where('published_at', '>=', now()->subDays(14))
                ->selectRaw('DATE(published_at) as day, COUNT(*) as total')
                ->groupBy('day')
                ->pluck('total', 'day')
        );

        return view('admin.dashboard', [
            'published' => $published,
            'pendingReview' => $pendingReview,
            'scheduled' => $scheduled,
            'pendingComments' => $pendingComments,
            'totalViews' => $totalViews,
            'latestPosts' => $latestPosts,
            'categoryStats' => $categoryStats,
            'dailyCounts' => $dailyCounts,
        ]);
    }
}