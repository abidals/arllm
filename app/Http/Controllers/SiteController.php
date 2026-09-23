<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(): View
    {
        $featured = Post::published()
            ->with(['author', 'category'])
            ->where('is_featured', true)
            ->latest('published_at')
            ->limit(1)
            ->first();

        $sponsored = Post::published()
            ->with(['category'])
            ->where('is_sponsored', true)
            ->latest('published_at')
            ->limit(1)
            ->first();

        $latest = Post::published()
            ->with(['author', 'category'])
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->limit(21)
            ->get();

        $mostRead = Post::published()
            ->with(['category'])
            ->orderByDesc('views')
            ->limit(5)
            ->get();

        return view('site.index', [
            'featured' => $featured,
            'sponsored' => $sponsored,
            'posts' => $latest,
            'mostRead' => $mostRead,
        ]);
    }

    public function category(Category $category, Request $request): View
    {
        abort_unless($category->is_active, 404);

        $posts = $category->publishedPosts()
            ->with(['author', 'category'])
            ->latest('published_at')
            ->paginate(12);

        return view('site.category', ['category' => $category, 'posts' => $posts]);
    }

    public function post(Post $post, Request $request): View
    {
        $post->load(['author', 'category', 'tags']);

        if (!$request->user()) {
            $post->timestamps = false;
            $post->increment('views');
            $post->timestamps = true;
        }

        $comments = $post->approvedComments()->get();

        return view('site.post', [
            'post' => $post,
            'related' => $post->relatedPosts(),
            'comments' => $comments,
        ]);
    }

    public function author(User $author): View
    {
        $posts = $author->posts()->published()
            ->with(['category'])
            ->latest('published_at')
            ->paginate(12);

        return view('site.author', ['author' => $author, 'posts' => $posts]);
    }

    public function search(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $posts = collect();

        if ($q !== '') {
            $q = mb_substr($q, 0, 100);
            $posts = Post::published()
                ->with(['author', 'category'])
                ->where(fn ($query) => $query
                    ->where('title', 'like', "%{$q}%")
                    ->orWhere('subtitle', 'like', "%{$q}%")
                    ->orWhere('excerpt', 'like', "%{$q}%")
                    ->orWhere('content_md', 'like', "%{$q}%"))
                ->latest('published_at')
                ->paginate(12)
                ->withQueryString();
        }

        return view('site.search', ['q' => $q, 'posts' => $posts]);
    }

    public function archives(Request $request): View
    {
        $monthParam = $request->query('month');

        $selected = null;
        $posts = collect();
        $selectedMonth = null;
        $selectedYear = null;

        if ($monthParam && preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
            $startDate = \Illuminate\Support\Carbon::parse($monthParam.'-01')->startOfDay();
            $endDate = (clone $startDate)->addMonth();
            $selectedMonth = (int) $startDate->format('n');
            $selectedYear = (int) $startDate->format('Y');
            $selected = $monthParam;

            $posts = Post::published()
                ->with('category')
                ->whereBetween('published_at', [$startDate, $endDate])
                ->latest('published_at')
                ->get();
        }

        $groups = Post::published()
            ->selectRaw("DATE_FORMAT(published_at, '%Y-%m') AS month, COUNT(*) AS total")
            ->groupBy('month')
            ->orderByDesc('month')
            ->limit(24)
            ->get();

        return view('site.archives', [
            'groups' => $groups,
            'selected' => $selected,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'posts' => $posts,
        ]);
    }
}