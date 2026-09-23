<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            نتائج البحث @if ($q) عن «{{ $q }}» @endif
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <form action="{{ route('admin.search') }}" method="GET" class="flex gap-2">
            <input type="search" name="q" value="{{ $q }}" placeholder="ابحث في كل المقالات والصفحات والأقسام والإعدادات (تاريخ عدد بصيغة 2026-09-23)…" maxlength="100"
                   class="flex-1 border-gray-300 rounded-lg px-4 py-2.5 focus:border-gray-900 focus:ring-gray-900">
            <button class="bg-brand hover-brand-dark text-white px-6 py-2.5 rounded-lg font-semibold text-white">بحث</button>
        </form>

        @if ($q === '')
            <div class="bg-white rounded-xl shadow p-10 text-center text-gray-500">
                اكتب كلمة للبحث في كل بيانات الموقع — المقالات بكل حالاتها، الصفحات، الأقسام، المستخدمون، أو تاريخ عدد محدد.
            </div>
        @else
            {{-- المقالات --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <h3 class="font-bold px-5 py-3 border-b border-gray-100">المقالات ({{ $posts->count() }})</h3>
                <div class="divide-y divide-gray-100">
                    @forelse ($posts as $post)
                        <div class="flex items-start justify-between gap-4 px-5 py-3 hover:bg-gray-50">
                            <div class="min-w-0">
                                <a href="{{ route('admin.posts.edit', $post) }}" class="font-semibold text-sm hover:text-brand line-clamp-1">{{ $post->title }}</a>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $post->author?->approvedName() }} · {{ $post->category?->name }} ·
                                    {{ ['draft' => 'مسودة', 'review' => 'مراجعة', 'scheduled' => 'مجدول', 'published' => 'منشور', 'archived' => 'أرشيف'][$post->status] ?? $post->status }}
                                    @if ($post->published_at) · {{ $post->publishDateShort() }} @endif
                                </p>
                            </div>
                            <a href="{{ route('site.post', $post->slug) }}" target="_blank" rel="noopener" class="text-xs text-blue-700 hover:underline shrink-0">عرض ↗</a>
                        </div>
                    @empty
                        <p class="px-5 py-6 text-sm text-gray-500">لا مقالات مطابقة.</p>
                    @endforelse
                </div>
            </div>

            @if (auth()->user()->can('manage-pages'))
                {{-- الصفحات --}}
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <h3 class="font-bold px-5 py-3 border-b border-gray-100">الصفحات ({{ $pages->count() }})</h3>
                    <ul class="divide-y divide-gray-100">
                        @forelse ($pages as $page)
                            <li class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                                <div>
                                    <span class="font-semibold text-sm">{{ $page->title }}</span>
                                    <code class="text-[11px] text-gray-400 ms-2">/page/{{ $page->slug }}</code>
                                </div>
                                <a href="{{ route('site.page', $page->slug) }}" target="_blank" rel="noopener" class="text-xs text-blue-700 hover:underline">عرض ↗</a>
                            </li>
                        @empty
                            <li class="px-5 py-6 text-sm text-gray-500">لا صفحات مطابقة.</li>
                        @endforelse
                    </ul>
                </div>
            @endif

            {{-- الأقسام --}}
            @can('manage-categories')
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <h3 class="font-bold px-5 py-3 border-b border-gray-100">الأقسام ({{ $categories->count() }})</h3>
                    <ul class="divide-y divide-gray-100">
                        @forelse ($categories as $category)
                            <li class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 text-sm">
                                <span class="font-semibold">{{ $category->name }} @if ($category->parent) <span class="text-gray-400 text-xs">(فرع من {{ $category->parent->name }})</span> @endif</span>
                                <a href="{{ route('site.category', $category->slug) }}" target="_blank" rel="noopener" class="text-xs text-blue-700 hover:underline">عرض ↗</a>
                            </li>
                        @empty
                            <li class="px-5 py-6 text-sm text-gray-500">لا أقسام مطابقة.</li>
                        @endforelse
                    </ul>
                </div>
            @endcan

            {{-- المستخدمون --}}
            @can('manage-users')
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <h3 class="font-bold px-5 py-3 border-b border-gray-100">المستخدمون ({{ $users->count() }})</h3>
                    <ul class="divide-y divide-gray-100">
                        @forelse ($users as $u)
                            <li class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 text-sm">
                                <span class="font-semibold">{{ $u->approvedName() }} <span class="text-gray-400 text-xs">{{ $u->email }}</span></span>
                                <a href="{{ route('admin.users.edit', $u) }}" class="text-xs text-blue-700 hover:underline">تعديل</a>
                            </li>
                        @empty
                            <li class="px-5 py-6 text-sm text-gray-500">لا مستخدمين مطابقين.</li>
                        @endforelse
                    </ul>
                </div>
            @endcan

            {{-- الأعداد بالتاريخ --}}
            @if ($issuePosts->isNotEmpty())
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <h3 class="font-bold px-5 py-3 border-b border-gray-100">
                        مقالات الإصدار ({{ $issuePosts->count() }})
                        <a href="{{ route('site.issue', \Illuminate\Support\Str::of($q)->substr(0, 10)) }}" target="_blank" rel="noopener" class="text-xs text-brand font-semibold ms-2 hover:underline">فتح العدد ↗</a>
                    </h3>
                    <ul class="divide-y divide-gray-100">
                        @foreach ($issuePosts as $post)
                            <li class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                                <span class="text-sm font-medium">{{ $post->title }}</span>
                                <span class="text-xs text-gray-500">{{ $post->publishDate() }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif
    </div>
</x-app-layout>