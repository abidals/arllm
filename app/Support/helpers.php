<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

if (!function_exists('str_word_count_ar')) {
    /** Count words in Arabic or any Unicode text. */
    function str_word_count_ar(string $text): int
    {
        $text = strip_tags($text);
        preg_match_all('/[\p{L}\p{N}_]+/u', $text, $matches);

        return count($matches[0] ?? []);
    }
}

if (!function_exists('format_bytes')) {
    function format_bytes(int $bytes): string
    {
        $units = ['بايت', 'كيلوبايت', 'ميغابايت', 'غيغابايت'];

        return round($bytes / pow(1024, $i = floor(log(max($bytes, 1), 1024))), 1).' '.$units[$i];
    }
}

if (!function_exists('site_sections')) {
    /** أقسام الموقع الرئيسية فقط (الأبناء تصبح داخل صفحة القسم) */
    function site_sections(): array
    {
        try {
            $data = Cache::rememberForever('site.sections.v2', function () {
                return \App\Models\Category::query()
                    ->where('is_active', true)
                    ->whereNull('parent_id')
                    ->orderBy('sort_order')
                    ->limit(12)
                    ->get(['name', 'slug'])
                    ->map(fn ($c) => ['name' => $c->name, 'slug' => $c->slug])
                    ->values()
                    ->all();
            });

            return is_array($data) ? $data : [];
        } catch (\Throwable) {
            return [];
        }
    }
}

if (!function_exists('arabic_date')) {
    function arabic_date($date = null, string $format = 'dddd، D MMMM YYYY'): string
    {
        return ($date ?? now())->locale('ar')->isoFormat($format);
    }
}

if (!function_exists('render_markdown')) {
    /** تحويل Markdown إلى HTML آمن (بدون HTML خام) */
    function render_markdown(string $markdown): string
    {
        return \App\Services\MarkdownRenderer::render($markdown);
    }
}