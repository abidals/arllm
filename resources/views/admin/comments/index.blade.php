<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">مراجعة التعليقات @if ($pendingCount) <span class="bg-red-600 text-white text-xs px-2 py-0.5 rounded-full">{{ $pendingCount }}</span> @endif</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex gap-2 mb-6">
            @foreach (['pending' => 'بانتظار المراجعة', 'approved' => 'المعتمدة', 'spam' => 'مزعجة', 'all' => 'الكل'] as $key => $label)
                <a href="{{ route('admin.comments.index', ['filter' => $key]) }}"
                   class="px-4 py-2 text-sm font-semibold rounded-lg border {{ $filter === $key ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200 hover:border-gray-400' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="space-y-4">
            @forelse ($comments as $comment)
                <div class="bg-white rounded-xl shadow p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="font-bold">{{ $comment->name ?? 'زائر' }}</span>
                                @if ($comment->email)
                                    <span class="text-gray-500">{{ $comment->email }}</span>
                                @endif
                                <span class="text-gray-400 text-xs">· {{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-gray-700 mt-2 leading-relaxed font-naskh whitespace-pre-line">{{ $comment->body }}</p>
                            <p class="text-xs text-gray-400 mt-3">
                                على مقال:
                                <a href="{{ route('site.post', $comment->post?->slug) }}" target="_blank" rel="noopener" class="text-blue-700 hover:underline">
                                    {{ $comment->post?->title }}
                                </a>
                            </p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if ($comment->status !== 'approved')
                                <form method="POST" action="{{ route('admin.comments.approve', $comment) }}">
                                    @csrf
                                    <button class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg">اعتماد</button>
                                </form>
                            @endif
                            @if ($comment->status !== 'spam')
                                <form method="POST" action="{{ route('admin.comments.spam', $comment) }}">
                                    @csrf
                                    <button class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg">مزعج</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}"
                                  onsubmit="return confirm('حذف التعليق نهائيًا؟')">
                                @csrf
                                @method('DELETE')
                                <button class="bg-red-700 hover:bg-red-800 text-white text-xs font-semibold px-3 py-1.5 rounded-lg">حذف</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow p-12 text-center text-gray-500">
                    لا توجد تعليقات في هذه الفئة. 🎉
                </div>
            @endforelse
        </div>

        <div class="mt-6">{{ $comments->links() }}</div>
    </div>
</x-app-layout>