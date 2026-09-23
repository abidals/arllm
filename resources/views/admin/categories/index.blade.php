<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">أقسام الموقع</h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 grid lg:grid-cols-[320px_1fr] gap-6 items-start">
        {{-- إضافة قسم --}}
        <form method="POST" action="{{ route('admin.categories.store') }}" class="bg-white rounded-xl shadow p-6 space-y-4">
            @csrf
            <h3 class="font-bold border-b border-gray-200 pb-3">قسم جديد</h3>
            <div>
                <label for="name" class="block text-sm font-bold mb-1.5">اسم القسم <span class="text-red-600">*</span></label>
                <input id="name" name="name" required maxlength="100" class="w-full border-gray-300 rounded-lg">
            </div>
            <div>
                <label for="description" class="block text-sm font-bold mb-1.5">الوصف</label>
                <input id="description" name="description" maxlength="300" class="w-full border-gray-300 rounded-lg">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="sort_order" class="block text-sm font-bold mb-1.5">الترتيب</label>
                    <input id="sort_order" name="sort_order" type="number" min="0" max="255" value="{{ count($categories) }}"
                           class="w-full border-gray-300 rounded-lg">
                </div>
                <label class="flex items-center gap-2 text-sm mt-6">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-red-700">
                    مفعّل
                </label>
            </div>
            <button class="w-full bg-red-700 hover:bg-red-800 text-white font-semibold py-2.5 rounded-lg">إضافة القسم</button>
            @error('name') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
        </form>

        {{-- القائمة --}}
        <div class="space-y-3">
            @if (session('status'))
                <div class="rounded bg-green-50 border border-green-200 text-green-800 px-4 py-2.5 text-sm">{{ session('status') }}</div>
            @endif
            @if (session('errors')?->any())
                <div class="rounded bg-red-50 border border-red-200 text-red-800 px-4 py-2.5 text-sm">{{ session('errors')->first() }}</div>
            @endif

            @forelse ($categories as $category)
                <div class="bg-white rounded-xl shadow p-5">
                    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="grid sm:grid-cols-[1fr_1fr_auto_auto] gap-3 items-end">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">الاسم</label>
                            <input name="name" required maxlength="100" value="{{ $category->name }}" class="w-full border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">الوصف</label>
                            <input name="description" maxlength="300" value="{{ $category->description }}" class="w-full border-gray-300 rounded-lg">
                        </div>
                        <div class="w-20">
                            <label class="block text-xs font-bold text-gray-500 mb-1">ترتيب</label>
                            <input name="sort_order" type="number" min="0" max="255" value="{{ $category->sort_order }}" class="w-full border-gray-300 rounded-lg">
                            <input type="hidden" name="is_active" value="1">
                        </div>
                        <div class="flex gap-2">
                            <button class="bg-gray-900 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-gray-700">حفظ</button>
                        </div>
                    </form>
                    <div class="flex items-center justify-between mt-3 text-xs text-gray-500">
                        <span>{{ $category->posts_count }} مقال · <code>/section/{{ $category->slug }}</code></span>
                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                              onsubmit="return confirm('حذف القسم؟')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-700 hover:underline font-medium">حذف</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow p-12 text-center text-gray-500">لا توجد أقسام بعد.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>