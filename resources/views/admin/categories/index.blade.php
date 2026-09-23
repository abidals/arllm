<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">أقسام الموقع <span class="text-xs text-gray-500 font-normal">— اسحب للترتيب أو للنقل تحت قسم آخر</span></h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 grid lg:grid-cols-[320px_1fr] gap-6 items-start" data-cats-root>
        {{-- إضافة قسم --}}
        <form method="POST" action="{{ route('admin.categories.store') }}" class="bg-white rounded-xl shadow p-6 space-y-4">
            @csrf
            <h3 class="font-bold border-b border-gray-200 pb-3">قسم جديد</h3>
            <div>
                <label for="name" class="block text-sm font-bold mb-1.5">اسم القسم <span class="text-red-600">*</span></label>
                <input id="name" name="name" required maxlength="100" class="w-full border-gray-300 rounded-lg">
            </div>
            <div>
                <label for="parent_select" class="block text-sm font-bold mb-1.5">القسم الأب (اختياري)</label>
                <select id="parent_select" name="parent_id" class="w-full border-gray-300 rounded-lg bg-white">
                    <option value="">— قسم رئيسي —</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="description" class="block text-sm font-bold mb-1.5">الوصف</label>
                <input id="description" name="description" maxlength="300" class="w-full border-gray-300 rounded-lg">
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-red-700">
                مفعّل
            </label>
            <button class="w-full bg-red-700 hover:bg-red-800 text-white font-semibold py-2.5 rounded-lg">إضافة القسم</button>
            @error('name') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
        </form>

        {{-- الشجرة القابلة للسحب --}}
        <div class="space-y-4">
            @if (session('status'))
                <div class="rounded bg-green-50 border border-green-200 text-green-800 px-4 py-2.5 text-sm">{{ session('status') }}</div>
            @endif
            @if (session('errors')?->any())
                <div class="rounded bg-red-50 border border-red-200 text-red-800 px-4 py-2.5 text-sm">{{ session('errors')->first() }}</div>
            @endif

            <div class="bg-white rounded-xl shadow p-5" id="drag-message" class="hidden"></div>

            <div data-cats-tree
                 class="bg-white rounded-xl shadow p-5 space-y-5">
                <p class="text-xs text-gray-500 -mt-2">اسحب أي بطاقة لإعادة ترتيبها. إفلاتها داخل «الأبناء» في قسم آخر = تصبح فرعًا له.</p>

                <div data-cats-list data-parent="top" class="space-y-5">
                    @forelse ($parents as $parent)
                    <div data-cat-row data-id="{{ $parent->id }}" draggable="true" class="border border-gray-200 rounded-xl overflow-hidden bg-white">
                        <div class="bg-gray-50 px-3 py-2 flex items-center justify-between gap-3">
                            <form method="POST" action="{{ route('admin.categories.update', $parent) }}" class="flex-1 flex items-center gap-2" data-no-drag>
                                @csrf
                                @method('PUT')
                                <span class="cursor-grab select-none text-gray-400 text-sm">⠿</span>
                                <input name="name" value="{{ $parent->name }}" maxlength="100"
                                       class="font-bold flex-1 border-transparent hover:border-gray-300 focus:border-gray-900 rounded px-2 py-1 text-sm bg-transparent">
                                <button class="text-xs bg-gray-900 text-white px-3 py-1.5 rounded-lg hover:bg-gray-700">حفظ</button>
                            </form>
                        </div>

                        {{-- منطقة الأبناء --}}
                        <div data-cats-list data-parent="{{ $parent->id }}" class="min-h-[46px] p-3 space-y-2 bg-white transition-colors">
                            @foreach ($parent->children as $child)
                                <div data-cat-row data-id="{{ $child->id }}" draggable="true"
                                     class="bg-gray-50 hover:bg-blue-50 border border-gray-100 rounded-lg px-3 py-2 flex items-center justify-between gap-2 cursor-grab active:cursor-grabbing">
                                    <form method="POST" action="{{ route('admin.categories.update', $child) }}" class="flex-1 flex items-center gap-2" data-no-drag>
                                        @csrf
                                        @method('PUT')
                                        <span class="text-gray-400 text-xs select-none">⇱</span>
                                        <input name="name" value="{{ $child->name }}" maxlength="100"
                                               class="text-sm flex-1 border-transparent hover:border-gray-300 focus:border-gray-900 rounded px-2 py-1 bg-transparent">
                                        <button class="text-[11px] bg-white border border-gray-200 px-2 py-1 rounded hover:bg-gray-800 hover:text-white">حفظ</button>
                                    </form>
                                    <div class="text-[10px] text-gray-400">{{ $child->posts_count ?? 0 }} مقال</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="px-3 py-2 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
                            <span>{{ $parent->posts_count }} مقال رئيسي · <code>/section/{{ $parent->slug }}</code></span>
                            <form method="POST" action="{{ route('admin.categories.destroy', $parent) }}"
                                  onsubmit="return confirm('حذف القسم والفرعية الفارغة؟')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-700 hover:underline font-medium">حذف</button>
                            </form>
                        </div>
                    </div>
                    @empty
                        <div class="text-gray-400 py-8 text-center text-sm">اسحب الأقسام الرئيسية هنا للترتيب.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>