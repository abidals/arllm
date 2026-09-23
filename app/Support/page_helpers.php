<?php

use App\Models\Page;
use Illuminate\Support\Facades\Cache;

if (!function_exists('nav_pages')) {
    /** صفحات تُعرض في التذييل */
    function nav_pages(): array
    {
        try {
            $data = Cache::rememberForever('nav.pages', function () {
                return Page::query()
                    ->where('is_published', true)
                    ->where('show_in_nav', true)
                    ->orderBy('sort_order')
                    ->get(['title', 'slug'])
                    ->map(fn ($p) => ['title' => $p->title, 'slug' => $p->slug])
                    ->all();
            });

            return is_array($data) ? $data : [];
        } catch (\Throwable) {
            return [];
        }
    }
}