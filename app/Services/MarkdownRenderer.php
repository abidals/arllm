<?php

namespace App\Services;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

/**
 * محرك تحويل Markdown آمن — يعمل مشتركًا بين المقالات والصفحات.
 * HTML الخام يُزال (strip) والروابط الخطرة تُمنع.
 */
class MarkdownRenderer
{
    private static ?MarkdownConverter $converter = null;

    public static function converter(): MarkdownConverter
    {
        if (!self::$converter) {
            $env = new Environment([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
            $env->addExtension(new CommonMarkCoreExtension());
            $env->addExtension(new GithubFlavoredMarkdownExtension());
            $env->addExtension(new AutolinkExtension());
            self::$converter = new MarkdownConverter($env);
        }

        return self::$converter;
    }

    public static function render(string $markdown): string
    {
        return self::converter()->convert($markdown)->getContent();
    }
}