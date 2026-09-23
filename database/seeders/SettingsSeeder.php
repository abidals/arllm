<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'site_tagline' => 'صحافة رقمية عربية: تحليلات، اقتصاد، وتقنية كل يوم',
            'site_description' => 'مدار — موقع إخباري وتحليلي عربي ينشر ملفات ومقالات يومية في السياسة والاقتصاد والتقنية والثقافة.',
            'sponsored_label' => 'محتوى إعلاني',
            'footer_text' => 'جميع الحقوق محفوظة',
            'comments_enabled' => '1',
        ];

        foreach ($defaults as $key => $value) {
            Setting::set($key, $value);
        }
    }
}