<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'sort_order', 'is_active', 'parent_id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function scopeParents(Builder $query): Builder
    {
        return $query->whereNull('parent_id')->orderBy('sort_order');
    }

    public function allPostIds(): array
    {
        return $this->children()->pluck('id')->prepend($this->id)->all();
    }

    public function publishedPosts()
    {
        return $this->hasMany(Post::class)->published();
    }

    public static function flushCache(): void
    {
        Cache::forget('site.sections');
        Cache::forget('site.sections.v2');
        Cache::forget('site.sections.v3');
    }
}