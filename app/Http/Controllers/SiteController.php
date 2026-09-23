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

        // شريط المميزات: مقالات مميزة أخرى (أنواع إبراز متعددة)
        $featuredStrip = Post::published()
            ->with(['author', 'category'])
            ->where('is_featured', true)
            ->when($featured, fn ($q) => $q->where('id', '!=', $featured->id))
            ->latest('published_at')
            ->limit(max(0, (int) \App\Models\Setting::get('featured_strip_count', '3')))
            ->get();

        $sponsored = Post::published()
            ->with(['category'])
            ->where('is_sponsored', true)
            ->latest('published_at')
            ->limit(1)
            ->first();

        $latest = Post::published()
            ->with(['author', 'category'])
            ->latest('published_at')
            ->where('is_featured', false)
            ->limit(21)
            ->get();

        $mostRead = Post::published()
            ->with(['category'])
            ->orderByDesc('views')
            ->limit(5)
            ->get();

        $ticker = \App\Models\Setting::get('ticker_enabled', '0') === '1' ? [
            'lines' => collect(preg_split('/[\r\n|]+/u', (string) \App\Models\Setting::get('ticker_text', '')))
                ->map(fn ($line) => trim($line))
                ->filter()
                ->values(),
            'speed' => max(10, (int) \App\Models\Setting::get('ticker_speed', '30')),
            'bg' => \App\Models\Setting::get('ticker_bg', '#111827'),
            'color' => \App\Models\Setting::get('ticker_color', '#f9fafb'),
        ] : null;

        return view('site.index', [
            'featured' => $featured,
            'featuredStrip' => $featuredStrip,
            'sponsored' => $sponsored,
            'posts' => $latest,
            'mostRead' => $mostRead,
            'ticker' => $ticker,
        ]);
    }

    public function category(Category $category, Request $request): View
    {
        abort_unless($category->is_active, 404);

        // المقالات تُسحب من القسم وجميع أقسامه الفرعية
        $posts = Post::published()
            ->with(['author', 'category'])
            ->whereIn('category_id', $category->allPostIds())
            ->latest('published_at')
            ->paginate(12);

        return view('site.category', [
            'category' => $category,
            'children' => $category->children()->where('is_active', true)->get(),
            'posts' => $posts,
        ]);
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

    /**
     * صفحة العدد اليومي — مجلة احترافية لتغطية يوم واحد.
     */
    public function issue(Request $request): View
    {
        $date = $request->route('date');
        $day = null;

        if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            try {
                $day = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
            } catch (\Throwable) {
                abort(404);
            }
        } else {
            $day = now()->startOfDay();
        }

        $from = (clone $day)->startOfDay();
        $to = (clone $day)->endOfDay();

        $all = Post::published()
            ->with(['author', 'category'])
            ->whereBetween('published_at', [$from, $to])
            ->orderByDesc('is_featured')
            ->orderByDesc('views')
            ->get();

        $cover = $all->firstWhere('is_featured') && $all->first()->is_featured ? $all->first() : null;

        $grouped = $all
            ->when($cover, fn ($items) => $items->reject(fn ($p) => $p->id === $cover->id))
            ->groupBy(fn ($post) => $post->category?->name ?? 'عام');

        if ($day->isToday()) {
            $title = 'عدد اليوم';
        } else {
            $title = 'عدد '.$day->locale('ar')->isoFormat('D MMMM YYYY');
        }

        return view('site.issue', [
            'title' => $title,
            'day' => $day,
            'cover' => $cover,
            'sections' => $grouped,
        ]);
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