<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit(): \Illuminate\View\View
    {
        return view('admin.settings.edit', [
            'settings' => [
                'site_tagline' => Setting::get('site_tagline', ''),
                'site_description' => Setting::get('site_description', ''),
                'sponsored_label' => Setting::get('sponsored_label', 'محتوى إعلاني'),
                'footer_text' => Setting::get('footer_text', ''),
                'comments_enabled' => Setting::get('comments_enabled', '1') === '1',
                'ai_enabled' => Setting::get('ai_enabled', '0') === '1',
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_tagline' => ['nullable', 'string', 'max:150'],
            'site_description' => ['nullable', 'string', 'max:300'],
            'sponsored_label' => ['nullable', 'string', 'max:60'],
            'footer_text' => ['nullable', 'string', 'max:300'],
            'comments_enabled' => ['nullable', 'boolean'],
            'ai_enabled' => ['nullable', 'boolean'],
        ]);

        foreach ($data as $key => $value) {
            if (in_array($key, ['comments_enabled', 'ai_enabled'], true)) {
                $value = $value ? '1' : '0';
            }

            if (is_string($value)) {
                $value = strip_tags($value);
            }

            Setting::set($key, $value);
        }

        return back()->with('status', 'تم حفظ الإعدادات.');
    }
}