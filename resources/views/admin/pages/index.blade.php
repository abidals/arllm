<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">الصفحات الثابتة</h2>
            <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-gray-900">الموقع العام ←</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('admin.ai._panel', ['context' => 'page'])

        <div class="grid lg:grid-cols-[340px_1fr] gap-6 items-start">
        {{-- صفحة جديدة --}}
        <form method="POST" action="{{ route('admin.pages.store') }}" class="bg-white rounded-xl shadow p-6 space-y-4">
            @csrf
            <h3 class="font-bold border-b border-gray-200 pb-3">صفحة جديدة</h3>
            <div>
                <label class="block text-sm font-bold mb-1.5">العنوان <span class="text-red-600">*</span></label>
                <input name="title" required maxlength="200" class="w-full border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-bold mb-1.5">بينونة (slug اختياري)</label>
                <input name="slug" class="w-full border-gray-300 rounded-lg" placeholder="about-us">
            </div>
            <div>
                <label class="block text-sm font-bold mb-1.5">المحتوى (Markdown) <span class="text-red-600">*</span></label>
                <textarea name="content_md" rows="6" required class="w-full border-gray-300 rounded-lg font-mono text-sm"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" checked class="rounded border-gray-300 text-red-700">
                    منشورة
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="show_in_nav" value="0">
                    <input type="checkbox" name="show_in_nav" value="1" class="rounded border-gray-300 text-red-700">
                    في التذييل
                </label>
            </div>
            <button class="w-full bg-red-700 hover:bg-red-800 text-white font-semibold py-2.5 rounded-lg">إضافة</button>
        </form>

        {{-- القائمة --}}
        <div class="space-y-4">
            @forelse ($pages as $page)
                <div class="bg-white rounded-xl shadow p-5">
                    <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center justify-between">
                            <input name="title" value="{{ $page->title }}" class="font-bold w-full border-gray-300 rounded-lg max-w-md">
                            <div class="flex gap-3 text-xs">
                                <label class="flex items-center gap-1">
                                    <input type="hidden" name="is_published" value="0">
                                    <input type="checkbox" name="is_published" value="1" @checked($page->is_published) class="rounded border-gray-300 text-red-700"> منشورة
                                </label>
                                <label class="flex items-center gap-1">
                                    <input type="hidden" name="show_in_nav" value="0">
                                    <input type="checkbox" name="show_in_nav" value="1" @checked($page->show_in_nav) class="rounded border-gray-300 text-red-700"> تذييل
                                </label>
                            </div>
                        </div>
                        <textarea name="content_md" rows="5" class="w-full border-gray-300 rounded-lg font-mono text-sm font-naskh">{{ $page->content_md }}</textarea>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <code>/page/{{ $page->slug }}</code>
                            <div class="flex gap-2">
                                <button class="bg-gray-900 text-white font-semibold px-4 py-1.5 rounded-lg hover:bg-gray-700">حفظ</button>
                            </div>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="mt-2 text-left"
                          onsubmit="return confirm('حذف الصفحة نهائيًا؟')">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-700 hover:underline text-xs font-medium">حذف</button>
                    </form>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow p-12 text-center text-gray-500">لا توجد صفحات بعد.</div>
            @endforelse
        </div>
        </div>
    </div>
</x-app-layout>