<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /** خطوط Google العربية المتاحة للاختيار */
    public const GOOGLE_ARABIC_FONTS = [
        'IBM Plex Sans Arabic' => 'IBM Plex Sans Arabic',
        'Noto Kufi Arabic' => 'Noto Kufi Arabic',
        'Noto Naskh Arabic' => 'Noto Naskh Arabic',
        'Cairo' => 'Cairo',
        'Almarai' => 'Almarai',
        'Tajawal' => 'Tajawal',
        'Amiri' => 'Amiri',
        'Changa' => 'Changa',
        'Lalezar' => 'Lalezar',
        'Mada' => 'Mada',
        'Reem Kufi' => 'Reem Kufi',
        'Harmattan' => 'Harmattan',
        'Scheherazade New' => 'Scheherazade New',
        'Readex Pro' => 'Readex Pro',
    ];

    private const TEXT_KEYS = [
        'site_tagline' => '', 'site_description' => '', 'sponsored_label' => 'محتوى إعلاني',
        'footer_text' => '', 'ticker_text' => '', 'site_name' => '',
    ];

    public function edit(): \Illuminate\View\View
    {
        return view('admin.settings.edit', [
            'settings' => [
                'site_name' => Setting::get('site_name', 'مدار'),
                'site_tagline' => Setting::get('site_tagline', 'صحافة رقمية عربية'),
                'site_description' => Setting::get('site_description', ''),
                'sponsored_label' => Setting::get('sponsored_label', 'محتوى إعلاني'),
                'footer_text' => Setting::get('footer_text', ''),
                'comments_enabled' => Setting::get('comments_enabled', '1') === '1',
                'ai_enabled' => Setting::get('ai_enabled', '0') === '1',

                // الثيم والألوان
                'brand_color' => Setting::get('brand_color', '#b91c1c'),
                'dark_color' => Setting::get('dark_color', '#111827'),
                'menu_position' => Setting::get('menu_position', 'right'),

                // الهوية البصرية
                'site_logo' => Setting::get('site_logo'),
                'site_favicon' => Setting::get('site_favicon'),

                // الخطوط
                'font_heading_family' => Setting::get('font_heading_family', 'IBM Plex Sans Arabic'),
                'font_body_family' => Setting::get('font_body_family', 'Noto Naskh Arabic'),
                'heading_font_path' => Setting::get('heading_font_path'),
                'body_font_path' => Setting::get('body_font_path'),

                // شريط العاجل
                'ticker_enabled' => Setting::get('ticker_enabled', '0') === '1',
                'ticker_speed' => Setting::get('ticker_speed', '30'),
                'ticker_bg' => Setting::get('ticker_bg', '#111827'),
                'ticker_color' => Setting::get('ticker_color', '#f9fafb'),
                'ticker_text' => Setting::get('ticker_text', ''),

                // الصفحة الرئيسية
                'featured_strip_count' => Setting::get('featured_strip_count', '3'),
            ],
            'googleFonts' => self::GOOGLE_ARABIC_FONTS,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_name' => ['nullable', 'string', 'max:60'],
            'site_tagline' => ['nullable', 'string', 'max:150'],
            'site_description' => ['nullable', 'string', 'max:300'],
            'sponsored_label' => ['nullable', 'string', 'max:60'],
            'footer_text' => ['nullable', 'string', 'max:300'],
            'comments_enabled' => ['nullable', 'boolean'],
            'ai_enabled' => ['nullable', 'boolean'],

            'brand_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{3,8}$/'],
            'dark_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{3,8}$/'],
            'menu_position' => ['nullable', Rule::in(['right', 'center', 'left'])],

            'font_heading_family' => ['nullable', Rule::in(array_keys(self::GOOGLE_ARABIC_FONTS))],
            'font_body_family' => ['nullable', Rule::in(array_keys(self::GOOGLE_ARABIC_FONTS))],

            'ticker_enabled' => ['nullable', 'boolean'],
            'ticker_text' => ['nullable', 'string', 'max:2000'],
            'ticker_speed' => ['nullable', 'integer', 'between:10,120'],
            'ticker_bg' => ['nullable', 'regex:/^#[0-9a-fA-F]{3,8}$/'],
            'ticker_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{3,8}$/'],

            'featured_strip_count' => ['nullable', 'integer', 'between:0,6'],

            'site_logo' => ['nullable', 'file', 'max:1024', 'mimes:png,svg,jpg,webp'],
            'site_favicon' => ['nullable', 'file', 'max:512', 'mimes:png,ico,svg'],
            'heading_font_file' => ['nullable', 'file', 'max:4096', 'extensions:woff,woff2,ttf'],
            'body_font_file' => ['nullable', 'file', 'max:4096', 'extensions:woff,woff2,ttf'],
        ]);

        foreach (self::TEXT_KEYS as $key => $default) {
            if (array_key_exists($key, $data) && is_string($data[$key])) {
                Setting::set($key, strip_tags($data[$key]));
            }
        }

        foreach (['comments_enabled', 'ai_enabled', 'ticker_enabled'] as $flag) {
            if (array_key_exists($flag, $data)) {
                Setting::set($flag, $data[$flag] ? '1' : '0');
            }
        }

        foreach (['font_heading_family', 'font_body_family', 'ticker_speed', 'ticker_bg', 'ticker_color', 'featured_strip_count', 'brand_color', 'dark_color', 'menu_position'] as $simple) {
            if (isset($data[$simple])) {
                Setting::set($simple, (string) $data[$simple]);
            }
        }

        // ==================================================================
        // رفع الملفات (لوجو/فيكون/خطوط) — تحقق + تنقية + تخزين آمن
        // ==================================================================

        if ($request->hasFile('site_logo')) {
            $file = $request->file('site_logo');
            $name = 'logo-'.bin2hex(random_bytes(8)).'.'.strtolower($file->getClientOriginalExtension());

            if (strtolower($file->getClientOriginalExtension()) === 'svg') {
                $content = $this->sanitizeSvg($file->getContent());
                Storage::disk('public')->put('brand/'.$name, $content);
            } else {
                $file->storeAs('brand', $name, 'public');
            }

            // حذف القديم
            if ($old = Setting::get('site_logo')) {
                Storage::disk('public')->delete($old);
            }

            Setting::set('site_logo', 'brand/'.$name);
        }

        if ($request->hasFile('site_favicon')) {
            $file = $request->file('site_favicon');
            $name = 'favicon-'.bin2hex(random_bytes(8)).'.'.strtolower($file->getClientOriginalExtension());

            if (strtolower($file->getClientOriginalExtension()) === 'svg') {
                Storage::disk('public')->put('brand/'.$name, $this->sanitizeSvg($file->getContent()));
            } else {
                $file->storeAs('brand', $name, 'public');
            }

            if ($old = Setting::get('site_favicon')) {
                Storage::disk('public')->delete($old);
            }

            Setting::set('site_favicon', 'brand/'.$name);
        }

        foreach (['heading_font_file' => 'heading_font_path', 'body_font_file' => 'body_font_path'] as $fileKey => $pathKey) {
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);
                $name = bin2hex(random_bytes(8)).'-font.'.strtolower($file->getClientOriginalExtension());
                $file->storeAs('fonts', $name, 'public');

                if ($old = Setting::get($pathKey)) {
                    Storage::disk('public')->delete($old);
                }

                Setting::set($pathKey, 'fonts/'.$name);
            }
        }

        return back()->with('status', 'تم حفظ الإعدادات.');
    }

    /** تنقية SVG: إزالة الأسكربتات والأحداث والروابط الخطرة */
    private function sanitizeSvg(string $svg): string
    {
        $svg = preg_replace('/<script[\s\S]*?<\/script>/i', '', $svg);
        $svg = preg_replace('/\son[a-zA-Z]+\s*=\s*"[^"]*"/i', '', $svg);
        $svg = preg_replace('/\son[a-zA-Z]+\s*=\s*\'[^\']*\'/i', '', $svg);
        $svg = preg_replace('/(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\\2/i', '$1="#"', $svg);

        return $svg;
    }
}