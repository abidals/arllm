<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">مكتبة الوسائط {{ $assets->count() ? '('.count($assets).')' : '' }}</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 rounded bg-green-50 border border-green-200 text-green-800 px-4 py-2.5 text-sm">{{ session('status') }}</div>
        @endif
        @if (session('errors')?->any())
            <div class="mb-6 rounded bg-red-50 border border-red-200 text-red-800 px-4 py-2.5 text-sm">{{ session('errors')->first() }}</div>
        @endif

        {{-- رفع جديد --}}
        <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data"
              class="bg-white rounded-xl shadow p-6 mb-8 border-2 border-dashed border-gray-300 hover:border-gray-400 transition-colors">
            @csrf
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-[240px]">
                    <label class="block text-sm font-bold mb-1.5">أضف ملفات جديدة إلى المكتبة</label>
                    <input type="file" name="files[]" multiple accept=".jpg,.jpeg,.png,.webp,.gif,.svg,.pdf,.md,.txt"
                           class="block w-full text-sm border border-gray-300 rounded file:me-3 file:py-2 file:px-4 file:rounded file:border-0 file:bg-gray-900 file:text-white">
                    <p class="text-xs text-gray-500 mt-1">صور (jpg/png/webp/gif/svg) و PDF و md — حتى 4MB للملف. يُنقّى SVG تلقائيًا.</p>
                </div>
                <button class="bg-brand hover-brand-dark text-white font-semibold px-6 py-2.5 rounded-lg text-sm text-white">رفع إلى المكتبة</button>
            </div>
            @error('files.*') <p class="text-red-600 text-xs mt-2">{{ $message }}</p> @enderror
        </form>

        {{-- الشبكة --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @forelse ($assets as $asset)
                <div class="bg-white rounded-xl shadow overflow-hidden flex flex-col">
                    <a href="{{ Storage::url($asset->path) }}" target="_blank" rel="noopener" class="block bg-gray-100 aspect-video">
                        @php
                            $ext = strtolower(pathinfo($asset->path, PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                        @endphp
                        @if ($isImage)
                            <img src="{{ Storage::url($asset->path) }}" alt="{{ $asset->original_name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-4xl text-gray-400">
                                {{ $ext === 'pdf' ? '📄' : ($ext === 'md' ? '📝' : '📎') }}
                            </div>
                        @endif
                    </a>

                    <div class="p-4 flex-1 space-y-2">
                        {{-- إعادة تسمية --}}
                        <form method="POST" action="{{ route('admin.media.rename', $asset) }}" class="flex gap-1.5">
                            @csrf
                            <input name="original_name" value="{{ $asset->original_name }}" maxlength="100"
                                   class="flex-1 min-w-0 text-sm border-transparent hover:border-gray-300 focus:border-gray-900 rounded px-2 py-1 bg-gray-50">
                            <button class="text-[11px] bg-gray-900 text-white px-2 py-1 rounded hover:bg-gray-700 shrink-0">اسم</button>
                        </form>

                        <p class="text-[11px] text-gray-500" dir="ltr">
                            {{ number_format($asset->size / 1024, 1) }} KB · {{ $asset->mime_type }}
                        </p>

                        @if ($asset->usage->isNotEmpty())
                            <div class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1 leading-snug">
                                🔗 مستخدم في: {{ $asset->usage->pluck('label')->implode('، ') }}
                            </div>
                        @endif
                    </div>

                    <div class="px-4 pb-4 flex items-center gap-2">
                        {{-- استبدال --}}
                        <form method="POST" action="{{ route('admin.media.replace', $asset) }}" enctype="multipart/form-data"
                              class="flex-1" onsubmit="if(!this.querySelector('input[type=file]').value){alert('اختر ملف الاستبدال أولًا');return false}">
                            @csrf
                            <input type="file" name="replacement" required class="hidden" data-replace-input>
                            <button type="button" class="w-full text-[11px] border border-gray-300 rounded px-2 py-1.5 font-semibold hover:bg-gray-50"
                                    onclick="this.closest('form').querySelector('[data-replace-input]').click()"
                                    data-replace-btn>استبدال</button>
                        </form>

                        {{-- حذف --}}
                        @if ($asset->usage->isNotEmpty())
                            <form method="POST" action="{{ route('admin.media.force-destroy', $asset) }}"
                                  onsubmit="return confirm('حذف قسري؟ ستُزال الصورة من: {{ $asset->usage->pluck('label')->implode(' / ') }} — لا يتراجع.')">
                                @csrf
                                @method('DELETE')
                                <button class="text-[11px] text-red-700 border border-red-200 rounded px-2 py-1.5 font-semibold hover:bg-red-50 w-20">حذف قسري</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.media.destroy', $asset) }}"
                                  onsubmit="return confirm('حذف الملف نهائيًا من المكتبة؟')">
                                @csrf
                                @method('DELETE')
                                <button class="text-[11px] text-red-700 border border-red-200 rounded px-2 py-1.5 font-semibold hover:bg-red-50 w-20">حذف</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow p-12 text-center text-gray-500 col-span-4">
                    المكتبة فارغة — ارفع أول ملف من الأعلى.
                </div>
            @endforelse
        </div>
    </div>

    @vite(['resources/js/media-replace.js'])
</x-app-layout>