<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">إعدادات الموقع</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        {{-- شريط الهوية الحالية — نماذج الإزالة خارج نموذج الحفظ (التداخل يفسد DOM) --}}
        @if ($settings['site_logo'] || $settings['site_favicon'])
            <div class="bg-white rounded-xl shadow p-5 mb-5 flex flex-wrap items-center gap-4">
                <span class="text-xs font-black text-gray-400">الهوية الحالية:</span>
                @if ($settings['site_logo'])
                    <div class="flex items-center gap-2">
                        <img src="{{ Storage::url($settings['site_logo']) }}" alt="اللوجو" class="h-10 object-contain rounded border p-1 bg-white">
                        <form method="POST" action="{{ route('admin.settings.remove-logo') }}"
                              onsubmit="return confirm('إزالة اللوجو وحذف ملفه نهائيًا؟')">
                            @csrf
                            <button class="text-[11px] text-red-700 border border-red-200 rounded px-3 py-1.5 font-semibold hover:bg-red-50">
                                إزالة اللوجو
                            </button>
                        </form>
                    </div>
                @endif
                @if ($settings['site_favicon'])
                    <div class="flex items-center gap-2">
                        <img src="{{ Storage::url($settings['site_favicon']) }}" alt="الفيكون" class="h-9 w-9 object-contain rounded border p-1 bg-white">
                        <form method="POST" action="{{ route('admin.settings.remove-favicon') }}"
                              onsubmit="return confirm('إزالة الأيقونة وحذف ملفها نهائيًا؟')">
                            @csrf
                            <button class="text-[11px] text-red-700 border border-red-200 rounded px-3 py-1.5 font-semibold hover:bg-red-50">
                                إزالة الأيقونة
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data"
              onsubmit="return confirm('تأكيد حفظ الإعدادات الجديدة؟')"
              class="space-y-5">
            @csrf
            @method('PUT')

            @if (session('status'))
                <div class="rounded bg-green-50 border border-green-200 text-green-800 px-4 py-2.5 text-sm">{{ session('status') }}</div>
            @endif

            {{-- النصوص الأساسية --}}
            <div class="bg-white rounded-xl shadow p-6 space-y-4">
                <h3 class="font-bold border-b border-gray-200 pb-3">النصوص الأساسية (SEO)</h3>
                <div>
                    <label class="block text-sm font-bold mb-1.5">شعار الموقع (سطر تعريفي)</label>
                    <input name="site_tagline" maxlength="150" value="{{ old('site_tagline', $settings['site_tagline']) }}" class="w-full border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1.5">وصف الموقع (SEO)</label>
                    <textarea name="site_description" rows="2" maxlength="300" class="w-full border-gray-300 rounded-lg">{{ old('site_description', $settings['site_description']) }}</textarea>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold mb-1.5">تسمية المحتوى الإعلاني</label>
                        <input name="sponsored_label" maxlength="60" value="{{ old('sponsored_label', $settings['sponsored_label']) }}" class="w-full border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1.5">نص التذييل</label>
                        <input name="footer_text" maxlength="300" value="{{ old('footer_text', $settings['footer_text']) }}" class="w-full border-gray-300 rounded-lg">
                    </div>
                </div>
            </div>

            {{-- الهوية البصرية --}}
            <div class="bg-white rounded-xl shadow p-6 space-y-4">
                <h3 class="font-bold border-b border-gray-200 pb-3">الهوية البصرية (لوجو وأيقونة)</h3>
                <div class="grid sm:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-bold">لوجو الموقع</label>
                        @if ($settings['site_logo'])
                            <div class="flex items-center gap-3">
                                <img src="{{ Storage::url($settings['site_logo']) }}" alt="اللوجو الحالي" class="h-12 object-contain rounded border p-1 bg-white">
                                <span class="text-[11px] text-gray-500">الرفع الجديد يستبدله — أو أزله من الشريط أعلى الصفحة.</span>
                            </div>
                        @else
                            <p class="text-xs text-gray-500">لا يوجد لوجو — سيظهر اسم الموقع نصيًا.</p>
                        @endif
                        <input type="file" name="site_logo" accept=".png,.svg,.jpg,.webp"
                               class="block w-full text-xs border border-gray-300 rounded file:me-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-gray-900 file:text-white">
                        <p class="text-[11px] text-gray-500">PNG / SVG / JPG / WebP — حتى 1MB. يُسجّل أيضًا في مكتبة الوسائط.</p>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-bold">أيقونة المتصفح (Favicon)</label>
                        @if ($settings['site_favicon'])
                            <div class="flex items-center gap-3">
                                <img src="{{ Storage::url($settings['site_favicon']) }}" alt="الفيكون" class="h-10 w-10 object-contain rounded border p-1 bg-white">
                                <span class="text-[11px] text-gray-500">الرفع الجديد يستبدله — أو أزله من الشريط أعلى الصفحة.</span>
                            </div>
                        @else
                            <p class="text-xs text-gray-500">لا توجد أيقونة مخصصة.</p>
                        @endif
                        <input type="file" name="site_favicon" accept=".png,.ico,.svg"
                               class="block w-full text-xs border border-gray-300 rounded file:me-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-gray-900 file:text-white">
                        <p class="text-[11px] text-gray-500">PNG / ICO / SVG — حتى 512KB.</p>
                    </div>
                </div>
            </div>

            {{-- الثيم والألوان --}}
            <div class="bg-white rounded-xl shadow p-6 space-y-4">
                <h3 class="font-bold border-b border-gray-200 pb-3">اسم الموقع والألوان (الثيم الكامل)</h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold mb-1.5">اسم الموقع</label>
                        <input name="site_name" maxlength="60" value="{{ old('site_name', $settings['site_name']) }}" class="w-full border-gray-300 rounded-lg">
                        <p class="text-xs text-gray-500 mt-1">يظهر في الرأس والتذييل والعناوين — بدل الاسم الثابت في الكود.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1.5">موقع قائمة الأقسام</label>
                        <select name="menu_position" class="w-full border-gray-300 rounded-lg bg-white">
                            @foreach (['right' => 'يمين (افتراضي)', 'center' => 'وسط', 'left' => 'يسار'] as $pos => $label)
                                <option value="{{ $pos }}" @selected($settings['menu_position'] === $pos)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold mb-1.5">اللون الأساسي (هويات الروابط والفواصل)</label>
                        <input name="brand_color" type="color" value="{{ old('brand_color', $settings['brand_color']) }}" class="w-full h-10 rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1.5">اللون الداكن (الشريط والتذييل)</label>
                        <input name="dark_color" type="color" value="{{ old('dark_color', $settings['dark_color']) }}" class="w-full h-10 rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1.5">عدد مقالات الهيرو الرئيسية</label>
                        <input name="hero_count" type="number" min="1" max="3" value="{{ old('hero_count', $settings['hero_count']) }}" class="w-full border-gray-300 rounded-lg">
                        <p class="text-[11px] text-gray-500 mt-1">1 = غلاف كبير فقط · 2-3 = غلاف + بطاقات جانبية.</p>
                    </div>
                </div>
                <div class="grid sm:grid-cols-3 gap-3 border-t border-gray-100 pt-4">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 mb-1">عنوان التدفق الرئيسي</label>
                        <input name="home_title_latest" maxlength="60" value="{{ old('home_title_latest', $settings['home_title_latest']) }}" class="w-full border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 mb-1">عنوان الشريط المميز</label>
                        <input name="home_title_featured" maxlength="60" value="{{ old('home_title_featured', $settings['home_title_featured']) }}" class="w-full border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 mb-1">عنوان القائمة الجانبية</label>
                        <input name="home_title_most_read" maxlength="60" value="{{ old('home_title_most_read', $settings['home_title_most_read']) }}" class="w-full border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
            </div>

            {{-- الهيدر والفوتر --}}
            <div class="bg-white rounded-xl shadow p-6 space-y-4">
                <h3 class="font-bold border-b border-gray-200 pb-3">التحكم في الهيدر والفوتر</h3>
                <div class="grid sm:grid-cols-2 gap-x-8 gap-y-3">
                    <div class="space-y-2.5">
                        <p class="text-xs font-black text-gray-400">الهيدر</p>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="hidden" name="header_show_date" value="0">
                            <input type="checkbox" name="header_show_date" value="1" @checked($settings['header_show_date']) class="rounded border-gray-300 text-red-700">
                            إظهار التاريخ في الشريط العلوي
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="hidden" name="header_show_search" value="0">
                            <input type="checkbox" name="header_show_search" value="1" @checked($settings['header_show_search']) class="rounded border-gray-300 text-red-700">
                            إظهار زر العدسة (البحث)
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="hidden" name="header_sticky" value="0">
                            <input type="checkbox" name="header_sticky" value="1" @checked($settings['header_sticky']) class="rounded border-gray-300 text-red-700">
                            تثبيت الهيدر أعلى الشاشة عند التمرير
                        </label>
                    </div>
                    <div class="space-y-2.5">
                        <p class="text-xs font-black text-gray-400">الفوتر</p>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="hidden" name="footer_show_sections" value="0">
                            <input type="checkbox" name="footer_show_sections" value="1" @checked($settings['footer_show_sections']) class="rounded border-gray-300 text-red-700">
                            عمود الأقسام
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="hidden" name="footer_show_pages" value="0">
                            <input type="checkbox" name="footer_show_pages" value="1" @checked($settings['footer_show_pages']) class="rounded border-gray-300 text-red-700">
                            عمود الصفحات الثابتة
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="hidden" name="footer_show_links" value="0">
                            <input type="checkbox" name="footer_show_links" value="1" @checked($settings['footer_show_links']) class="rounded border-gray-300 text-red-700">
                            عمود الروابط السريعة
                        </label>
                    </div>
                </div>
            </div>

            {{-- الخطوط --}}
            <div class="bg-white rounded-xl shadow p-6 space-y-4">
                <h3 class="font-bold border-b border-gray-200 pb-3">الخطوط</h3>
                <p class="text-xs text-gray-500 -mt-2">اختر خط Google للعناوين وآخر للنصوص الداخلية — أو ارفع ملف خط خاص (woff2/ttf).</p>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold mb-1.5">خط العناوين</label>
                        <select name="font_heading_family" class="w-full border-gray-300 rounded-lg bg-white" style="font-family: '{{ $settings['font_heading_family'] }}', sans-serif">
                            @foreach ($googleFonts as $family => $name)
                                <option value="{{ $family }}" @selected($settings['font_heading_family'] === $family)>{{ $name }}</option>
                            @endforeach
                        </select>
                        @if ($settings['heading_font_path'])
                            <p class="text-[11px] text-green-700 mt-1">رفع مخصص: {{ basename($settings['heading_font_path']) }} (له الأولوية)</p>
                        @endif
                        <input type="file" name="heading_font_file" accept=".woff,.woff2,.ttf" class="block w-full text-xs border border-gray-300 rounded mt-2 file:me-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-gray-900 file:text-white file:text-[10px]">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1.5">خط النصوص الداخلية</label>
                        <select name="font_body_family" class="w-full border-gray-300 rounded-lg bg-white font-naskh" style="font-family: '{{ $settings['font_body_family'] }}', serif">
                            @foreach ($googleFonts as $family => $name)
                                <option value="{{ $family }}" @selected($settings['font_body_family'] === $family)>{{ $name }}</option>
                            @endforeach
                        </select>
                        @if ($settings['body_font_path'])
                            <p class="text-[11px] text-green-700 mt-1">رفع مخصص: {{ basename($settings['body_font_path']) }}</p>
                        @endif
                        <input type="file" name="body_font_file" accept=".woff,.woff2,.ttf" class="block w-full text-xs border border-gray-300 rounded mt-2 file:me-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-gray-900 file:text-white file:text-[10px]">
                    </div>
                </div>
            </div>

            {{-- شريط العاجل --}}
            <div class="bg-white rounded-xl shadow p-6 space-y-4">
                <h3 class="font-bold border-b border-gray-200 pb-3">شريط الأخبار العاجلة</h3>
                <label class="flex items-center gap-2">
                    <input type="hidden" name="ticker_enabled" value="0">
                    <input type="checkbox" name="ticker_enabled" value="1" @checked($settings['ticker_enabled']) class="rounded border-gray-300 text-red-700">
                    <span class="text-sm font-semibold">عرض شريط عاجل متحرك أعلى الصفحة الرئيسية</span>
                </label>
                <div>
                    <label class="block text-sm font-bold mb-1.5">العناوين (كل عنوان في سطر)</label>
                    <textarea name="ticker_text" rows="3" maxlength="2000" class="w-full border-gray-300 rounded-lg text-sm" placeholder="عاجل: اجتماع طارئ اليوم{{ "\n" }}عاجل: ثم تطوير في ملف الطاقة">{{ old('ticker_text', $settings['ticker_text']) }}</textarea>
                </div>
                <div class="grid sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-bold mb-1.5">السرعة (ثواني)</label>
                        <input name="ticker_speed" type="number" min="10" max="120" value="{{ old('ticker_speed', $settings['ticker_speed']) }}" class="w-full border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1.5">لون الخلفية</label>
                        <input name="ticker_bg" type="color" value="{{ old('ticker_bg', $settings['ticker_bg']) }}" class="w-full h-10 rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1.5">لون النص</label>
                        <input name="ticker_color" type="color" value="{{ old('ticker_color', $settings['ticker_color']) }}" class="w-full h-10 rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1.5">عدد «مميزة اليوم»</label>
                        <input name="featured_strip_count" type="number" min="0" max="6" value="{{ old('featured_strip_count', $settings['featured_strip_count']) }}" class="w-full border-gray-300 rounded-lg">
                        <p class="text-[11px] text-gray-500 mt-1">0 = إخفاء الشريط.</p>
                    </div>
                </div>
            </div>

            {{-- الميزات --}}
            <div class="bg-white rounded-xl shadow p-6 space-y-4">
                <h3 class="font-bold border-b border-gray-200 pb-3">الميزات</h3>
                <label class="flex items-center gap-2">
                    <input type="hidden" name="comments_enabled" value="0">
                    <input type="checkbox" name="comments_enabled" value="1" @checked($settings['comments_enabled']) class="rounded border-gray-300 text-red-700">
                    <span class="text-sm font-semibold">تمكين التعليقات في الموقع</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="hidden" name="ai_enabled" value="0">
                    <input type="checkbox" name="ai_enabled" value="1" @checked($settings['ai_enabled']) class="rounded border-gray-300 text-red-700">
                    <span class="text-sm font-semibold">تفعيل مساعد الذكاء الاصطناعي للتحرير (المدير والمحرر فقط)</span>
                </label>
            </div>

            <button class="w-full bg-red-700 hover:bg-red-800 text-white font-semibold py-2.5 rounded-lg">
                حفظ الإعدادات
            </button>
        </form>
    </div>
</x-app-layout>