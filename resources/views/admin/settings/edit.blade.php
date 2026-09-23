<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">إعدادات الموقع</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="bg-white rounded-xl shadow p-6 space-y-5">
            @csrf
            @method('PUT')

            @if (session('status'))
                <div class="rounded bg-green-50 border border-green-200 text-green-800 px-4 py-2.5 text-sm">{{ session('status') }}</div>
            @endif

            <div>
                <label for="site_tagline" class="block text-sm font-bold mb-1.5">شعار الموقع (سطر تعريفي)</label>
                <input id="site_tagline" name="site_tagline" maxlength="150" value="{{ old('site_tagline', $settings['site_tagline']) }}"
                       class="w-full border-gray-300 rounded-lg">
            </div>

            <div>
                <label for="site_description" class="block text-sm font-bold mb-1.5">وصف الموقع (SEO)</label>
                <textarea id="site_description" name="site_description" rows="2" maxlength="300"
                          class="w-full border-gray-300 rounded-lg">{{ old('site_description', $settings['site_description']) }}</textarea>
            </div>

            <div>
                <label for="sponsored_label" class="block text-sm font-bold mb-1.5">تسمية المحتوى الإعلاني</label>
                <input id="sponsored_label" name="sponsored_label" maxlength="60" value="{{ old('sponsored_label', $settings['sponsored_label']) }}"
                       class="w-full border-gray-300 rounded-lg">
            </div>

            <div>
                <label for="footer_text" class="block text-sm font-bold mb-1.5">نص التذييل</label>
                <input id="footer_text" name="footer_text" maxlength="300" value="{{ old('footer_text', $settings['footer_text']) }}"
                       class="w-full border-gray-300 rounded-lg">
            </div>

            <label class="flex items-center gap-2">
                <input type="hidden" name="comments_enabled" value="0">
                <input type="checkbox" name="comments_enabled" value="1" @checked($settings['comments_enabled'])
                       class="rounded border-gray-300 text-red-700 focus:ring-red-700">
                <span class="text-sm font-semibold">تمكين التعليقات في الموقع</span>
            </label>

            <button class="w-full bg-red-700 hover:bg-red-800 text-white font-semibold py-2.5 rounded-lg">
                حفظ الإعدادات
            </button>
        </form>
    </div>
</x-app-layout>