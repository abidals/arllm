<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">إدارة المقالات</h2>
            <a href="{{ route('admin.posts.create') }}" class="bg-red-700 hover:bg-red-800 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                + مقال جديد
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- فلاتر الحالة --}}
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach (['all' => 'الكل', 'draft' => 'مسوداتي', 'review' => 'بانتظار المراجعة', 'scheduled' => 'مجدولة', 'published' => 'منشورة'] as $key => $label)
                <a href="{{ route('admin.posts.index', ['status' => $key]) }}"
                    class="px-4 py-2 text-sm font-semibold rounded-lg border {{ $currentStatus === $key ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200 hover:border-gray-400' }}">
                    {{ $label }}
                    <span class="opacity-60">({{ $counts[$key] ?? 0 }})</span>
                </a>
            @endforeach
        </div>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-right font-semibold text-gray-600">العنوان</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-600">المؤلف</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-600 hidden sm:table-cell">القسم</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-600">الحالة</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-600 hidden md:table-cell">النشر</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($posts as $post)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 max-w-xs">
                                <a href="{{ route('admin.posts.edit', $post) }}" class="font-semibold text-gray-900 hover:text-red-700 line-clamp-1">{{ $post->title }}</a>
                            </td>
                            <td class="px-5 py-3 text-gray-600 whitespace-nowrap">{{ $post->author?->approvedName() }}</td>
                            <td class="px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $post->category?->name }}</td>
                            <td class="px-5 py-3">
                                @php
                                    $badges = [
                                        'draft' => 'bg-gray-100 text-gray-700',
                                        'review' => 'bg-yellow-100 text-yellow-800',
                                        'scheduled' => 'bg-blue-100 text-blue-800',
                                        'published' => 'bg-green-100 text-green-800',
                                        'archived' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $badges[$post->status] ?? '' }}">
                                    {{ ['draft' => 'مسودة', 'review' => 'مراجعة', 'scheduled' => 'مجدول', 'published' => 'منشور', 'archived' => 'أرشيف'][$post->status] ?? $post->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap hidden md:table-cell text-xs">
                                @if ($post->published_at)
                                    {{ $post->publishDateShort() }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-3 text-left whitespace-nowrap">
                                <a href="{{ route('admin.posts.edit', $post) }}" class="text-blue-700 hover:underline font-medium">تعديل</a>
                                @can('publish', $post)
                                    @if ($post->status !== 'published')
                                        <form method="POST" action="{{ route('admin.posts.publish', $post) }}" class="inline ms-2">
                                            @csrf
                                            <button class="text-green-700 hover:underline font-medium">نشر</button>
                                        </form>
                                    @endif
                                @endcan
                                @can('delete', $post)
                                    <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" class="inline ms-2"
                                          onsubmit="return confirm('هل تريد حذف هذا المقال فعلاً؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-700 hover:underline font-medium">حذف</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">لا توجد مقالات في هذه الحالة.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $posts->links() }}</div>
    </div>
</x-app-layout>