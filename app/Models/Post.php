<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

class Post extends Model
{
    use SoftDeletes;

    public const STATUS = ['draft', 'review', 'scheduled', 'published', 'archived'];

    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug', 'subtitle', 'excerpt',
        'content_md', 'content_html', 'featured_image', 'status',
        'is_featured', 'is_sponsored', 'reading_time', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_sponsored' => 'boolean',
            'published_at' => 'datetime',
            'reading_time' => 'integer',
        ];
    }

    /* ---------------- Relations ---------------- */

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments()
    {
        return $this->comments()->approved()->whereNull('parent_id')->with('replies');
    }

    /* ---------------- Scopes ---------------- */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /* ---------------- Markdown pipeline ---------------- */

    public function renderContent(): void
    {
        $this->content_html = render_markdown($this->content_md);
        $this->reading_time = max(1, (int) ceil(str_word_count_ar($this->content_md) / 180));
    }

    /* ---------------- Slug helper ---------------- */

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = self::buildSlug($title);
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private static function buildSlug(string $title): string
    {
        $ascii = \Illuminate\Support\Str::slug($title);

        if ($ascii !== '' && $ascii !== '-') {
            return \Illuminate\Support\Str::limit($ascii, 180, '');
        }

        $arabic = trim(preg_replace('/[^\p{Arabic}\p{N}\s-]/u', '', $title));
        $arabic = trim(preg_replace('/\s+/u', '-', $arabic), '-');

        return \Illuminate\Support\Str::limit($arabic !== '' ? $arabic : 'post', 180, '');
    }

    /* ---------------- Status pipeline ---------------- */

    /**
     * Determine post status from editor action and user role.
     */
    public function applyIncomingStatus(\Illuminate\Http\Request $request, \App\Models\User $user): void
    {
        $action = $request->input('status_action', 'draft');

        switch ($action) {
            case 'publish':
                if ($user->hasAnyRole(['admin', 'editor'])) {
                    $this->status = 'published';
                    $this->published_at = $request->filled('publish_at')
                        ? \Illuminate\Support\Carbon::parse($request->input('publish_at'))
                        : now();
                } else {
                    $this->status = 'review';
                }
                break;

            case 'schedule':
                if ($user->hasAnyRole(['admin', 'editor']) && $request->filled('publish_at')) {
                    $this->status = 'scheduled';
                    $this->published_at = \Illuminate\Support\Carbon::parse($request->input('publish_at'));
                } else {
                    $this->status = 'review';
                }
                break;

            case 'review':
                $this->status = 'review';
                break;

            default:
                $this->status = 'draft';
        }
    }

    /* ---------------- Helpers ---------------- */

    public function relatedPosts(int $limit = 4)
    {
        $relatedIds = Cache::remember("post.{$this->id}.related_ids.$limit", 300, function () use ($limit) {
            return static::published()
                ->where('id', '!=', $this->id)
                ->where(function ($q) {
                    $q->where('category_id', $this->category_id)
                        ->orWhereHas('tags', fn ($t) => $t->whereIn('tags.id', $this->tags->pluck('id')));
                })
                ->latest('published_at')
                ->limit($limit)
                ->pluck('id')
                ->all();
        });

        return static::published()
            ->with(['author', 'category'])
            ->whereIn('id', $relatedIds)
            ->latest('published_at')
            ->get();
    }

    public function publishDate(): string
    {
        return $this->published_at?->locale('ar')->isoFormat('D MMMM YYYY، hh:mm') ?? '';
    }

    public function publishDateShort(): string
    {
        return $this->published_at?->locale('ar')->isoFormat('D MMMM YYYY') ?? '';
    }
}