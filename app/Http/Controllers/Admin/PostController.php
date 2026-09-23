<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $status = $request->query('status', 'all');
        $user = Auth::user();

        $posts = Post::with(['author', 'category'])
            ->when(!$user->canPublish(), fn ($q) => $q->where('user_id', $user->id))
            ->when($status !== 'all' && in_array($status, Post::STATUS),
                fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.posts.index', [
            'posts' => $posts,
            'currentStatus' => $status,
            'counts' => [
                'all' => $user->canPublish() ? Post::count() : Post::where('user_id', $user->id)->count(),
                'draft' => $user->canPublish() ? Post::where('status', 'draft')->count() : Post::where('user_id', $user->id)->where('status', 'draft')->count(),
                'review' => Post::where('status', 'review')->count(),
                'scheduled' => Post::where('status', 'scheduled')->count(),
                'published' => Post::where('status', 'published')->count(),
            ],
        ]);
    }

    public function create(): \Illuminate\View\View
    {
        return view('admin.posts.create', ['categories' => Category::orderBy('sort_order')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePost($request);

        $post = new Post($validated);
        $post->user_id = Auth::id();
        $post->slug = Post::uniqueSlug($validated['title']);
        $post->content_md = $this->normalizeLineEndings($validated['content_md']);
        $post->excerpt = $validated['excerpt'] ?? $this->autoExcerpt($post->content_md);
        $post->renderContent();
        $post->applyIncomingStatus($request, Auth::user());
        $post->featured_image = $this->handleImage($request, $post);
        $post->save();
        $this->syncTags($post, $request->input('tags', ''));

        return redirect()->route('admin.posts.index')->with('status', 'تم إنشاء المقال بنجاح.');
    }

    public function edit(Post $post): \Illuminate\View\View
    {
        $this->authorize('update', $post);

        $post->load('tags');

        return view('admin.posts.edit', [
            'post' => $post,
            'categories' => Category::orderBy('sort_order')->get(),
            'tags' => $post->tags->pluck('name')->implode(', '),
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $validated = $this->validatePost($request, $post->id);

        $post->fill($validated);
        $post->slug = $validated['slug'] ?? null;
        if (empty($post->slug)) {
            $post->slug = Post::uniqueSlug($validated['title'], $post->id);
        }
        $post->content_md = $this->normalizeLineEndings($validated['content_md']);
        $post->excerpt = $validated['excerpt'] ?? $post->excerpt;
        $post->renderContent();
        $post->applyIncomingStatus($request, Auth::user());
        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $post->featured_image = $this->handleImage($request, $post);
        }
        $post->save();
        $this->syncTags($post, $request->input('tags', ''));

        return redirect()->route('admin.posts.edit', $post)->with('status', 'تم تحديث المقال.');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return back()->with('status', 'تم حذف المقال.');
    }

    /* ---------------- Allowed transitions ---------------- */

    public function submitReview(Post $post)
    {
        $this->authorize('update', $post);

        $post->status = 'review';
        $post->save();

        return back()->with('status', 'تم إرسال المقال للمراجعة.');
    }

    public function publish(Post $post)
    {
        $this->authorize('publish', $post);

        $post->status = 'published';
        $post->published_at ??= now();
        if ($post->published_at <= now() && $post->status !== 'scheduled') {
            // published now
        }
        $post->save();

        return back()->with('status', 'تم نشر المقال.');
    }

    public function unpublish(Post $post)
    {
        $this->authorize('publish', $post);

        $post->status = 'draft';
        $post->save();

        return back()->with('status', 'تم سحب المقال وسيعود إلى مسودات.');
    }

    /* ---------------- Markdown import ---------------- */

    public function importMarkdown(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:2048', 'mimes:md,markdown,txt', 'extensions:md,markdown,txt'],
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        if (mb_check_encoding($content, 'UTF-8') === false) {
            return back()->withErrors(['file' => 'الملف يجب أن يكون بترميز UTF-8.']);
        }

        return back()
            ->withInput()
            ->with('imported_markdown', $content)
            ->with('imported_title', pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME));
    }

    /* ---------------- Helpers ---------------- */

    private function handleImage(Request $request, ?Post $post = null): ?string
    {
        if (!$request->hasFile('featured_image')) {
            return $post?->featured_image;
        }

        $file = $request->file('featured_image');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            return $post?->featured_image;
        }

        $name = bin2hex(random_bytes(16)).'.'.$ext;

        $file->storeAs(
            'posts/'.now()->format('Y/m'),
            $name,
            'public'
        );

        return 'posts/'.now()->format('Y/m').'/'.$name;
    }

    private function validatePost(Request $request, ?int $postId = null): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:220', 'regex:/^[\p{L}\p{N}\-_\s]+$/u'],
            'subtitle' => ['nullable', 'string', 'max:200'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content_md' => ['required', 'string', 'max:200000'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'tags' => ['nullable', 'string', 'max:300'],
            'featured_image' => ['nullable'],
            'is_featured' => ['nullable', 'boolean'],
            'is_sponsored' => ['nullable', 'boolean'],
            'publish_at' => ['nullable', 'date', 'after:2020-01-01'],
            'status_action' => ['nullable', Rule::in(['draft', 'review', 'publish', 'schedule'])],
        ];

        if ($request->hasFile('featured_image')) {
            $rules['featured_image'] = ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'];
        }

        return $request->validate($rules);
    }

    private function normalizeLineEndings(string $s): string
    {
        return str_replace(["\r\n", "\r"], "\n", $s);
    }

    private function autoExcerpt(string $markdown): string
    {
        $plain = trim(strip_tags($markdown));
        $plain = preg_replace('/^#+\s+.*$/m', '', $plain);
        $plain = trim($plain);

        return \Illuminate\Support\Str::limit($plain, 160, '…');
    }

    private function syncTags(Post $post, ?string $tagString): void
    {
        $names = array_filter(array_map('trim', explode(',', (string) $tagString)));
        $ids = [];

        foreach (array_slice($names, 0, 8) as $name) {
            $name = mb_substr($name, 0, 60);
            $tag = Tag::firstOrCreate(
                ['name' => $name],
                ['slug' => \Illuminate\Support\Str::slug($name) ?: Post::uniqueSlug($name)]
            );
            $ids[] = $tag->id;
        }

        $post->tags()->sync($ids);
    }
}