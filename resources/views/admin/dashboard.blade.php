<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('لوحة التحكم') }} — مرحبًا {{ auth()->user()->approvedName() }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- الإحصائيات --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow p-5">
                <div class="w-2 h-2 rounded-full bg-green-500 mb-3"></div>
                <p class="text-sm text-gray-500">منشور</p>
                <p class="text-3xl font-bold mt-1">{{ $published }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <div class="w-2 h-2 rounded-full bg-yellow-400 mb-3"></div>
                <p class="text-sm text-gray-500">بانتظار المراجعة</p>
                <p class="text-3xl font-bold mt-1">{{ $pendingReview }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <div class="w-2 h-2 rounded-full bg-blue-500 mb-3"></div>
                <p class="text-sm text-gray-500">مجدولة</p>
                <p class="text-3xl font-bold mt-1">{{ $scheduled }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <div class="w-2 h-2 rounded-full bg-purple-500 mb-3"></div>
                <p class="text-sm text-gray-500">تعليقات جديدة</p>
                <p class="text-3xl font-bold mt-1">{{ $pendingComments }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <div class="w-2 h-2 rounded-full bg-gray-800 mb-3"></div>
                <p class="text-sm text-gray-500">إجمالي المشاهدات</p>
                <p class="text-3xl font-bold mt-1">{{ number_format($totalViews) }}</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            {{-- آخر المقالات --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="font-bold">أحدث المقالات</h3>
                    <a href="{{ route('admin.posts.create') }}" class="bg-red-700 hover:bg-red-800 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                        + مقال جديد
                    </a>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($latestPosts as $post)
                        <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                            <div class="min-w-0">
                                <a href="{{ route('admin.posts.edit', $post) }}" class="font-semibold text-sm hover:text-red-700 truncate block">
                                    {{ $post->title }}
                                </a>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $post->author?->approvedName() }} · {{ $post->category?->name }} · {{ \Illuminate\Support\Str::limit($post->published_at?->diffForHumans() ?? $post->created_at->diffForHumans(), 25) }}
                                </p>
                            </div>
                            @php
                                $badges = [
                                    'draft' => 'bg-gray-100 text-gray-700',
                                    'review' => 'bg-yellow-100 text-yellow-800',
                                    'scheduled' => 'bg-blue-100 text-blue-800',
                                    'published' => 'bg-green-100 text-green-800',
                                    'archived' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full shrink-0 {{ $badges[$post->status] ?? '' }}">
                                {{ [
                                    'draft' => 'مسودة', 'review' => 'مراجعة', 'scheduled' => 'مجدول',
                                    'published' => 'منشور', 'archived' => 'أرشيف',
                                ][$post->status] ?? $post->status }}
                            </span>
                        </div>
                    @empty
                        <p class="px-5 py-10 text-center text-gray-500">لا توجد مقالات بعد.</p>
                    @endforelse
                </div>
            </div>

            <div class="space-y-6">
                {{-- الأقسام الأكثر إنتاجًا --}}
                <div class="bg-white rounded-xl shadow p-5">
                    <h3 class="font-bold mb-4">الأقسام (آخر 30 يومًا)</h3>
                    <div class="space-y-3">
                        @forelse ($categoryStats as $name => $count)
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium">{{ $name }}</span>
                                    <span class="text-gray-500">{{ $count }}</span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-red-600 rounded-full" style="width: {{ $categoryStats->max() ? round(($count / $categoryStats->max()) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">لا بيانات بعد.</p>
                        @endforelse
                    </div>
                </div>

                {{-- تذكير الجدولة الذاتية --}}
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 text-sm">
                    <h3 class="font-bold text-blue-900 mb-2">النشر اليومي</h3>
                    <p class="text-blue-800 leading-relaxed">
                        يمكن جدولة المقالات ليُنشر مقال واحد يوميًّا في وقت الإصدار. تأكد من تعيين Cron في Hostinger لتشغيل أمر جدولة Laravel.
                    </p>
                    <code class="block mt-3 bg-blue-900 text-blue-100 text-xs rounded px-3 py-2 text-left overflow-x-auto" dir="ltr">
                        * * * * * php {{ base_path() }}/artisan schedule:run
                    </code>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>