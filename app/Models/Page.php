<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title', 'slug', 'subtitle', 'content_md', 'content_html',
        'is_published', 'show_in_nav', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'show_in_nav' => 'boolean',
        ];
    }

    public function renderContent(): void
    {
        $this->content_html = render_markdown($this->content_md);
    }

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

        $arabic = trim(trim(preg_replace('/[^\p{Arabic}\p{N}\s-]/u', '', $title)));
        $arabic = trim(preg_replace('/\s+/u', '-', $arabic), '-');

        return $arabic !== '' ? $arabic : 'page-'.\Illuminate\Support\Str::random(6);
    }
}