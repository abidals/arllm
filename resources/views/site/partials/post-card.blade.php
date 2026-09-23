<article class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
    <a href="{{ route('site.post', $post->slug) }}" class="block">
        <div class="aspect-video bg-gray-100 overflow-hidden">
            @if ($post->featured_image)
                <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" loading="lazy"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            @elseif(isset($showCategory, $post->category) && $post->category)
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-red-50 to-gray-100">
                    <span class="text-red-800 font-bold text-lg">{{ $post->category->name }}</span>
                </div>
            @endif
        </div>
        <div class="p-4">
            @if ($post->category)
                <span class="text-xs font-bold text-red-700">{{ $post->category->name }}</span>
            @endif
            <h3 class="font-bold text-lg leading-snug mt-1 group-hover:text-red-700 transition-colors line-clamp-2">
                {{ $post->title }}
            </h3>
            @if ($post->excerpt)
                <p class="text-sm text-gray-600 mt-2 leading-relaxed line-clamp-2 font-naskh">{{ $post->excerpt }}</p>
            @endif
            <div class="flex items-center gap-2 text-xs text-gray-500 mt-3">
                <span>{{ $post->author?->approvedName() }}</span>
                <span aria-hidden="true">•</span>
                <time datetime="{{ $post->published_at->toIso8601String() }}">{{ $post->publishDateShort() }}</time>
            </div>
        </div>
    </a>
</article>