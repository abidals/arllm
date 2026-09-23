<li class="flex gap-4 py-4 border-b border-gray-100 last:border-0">
    <span class="text-2xl font-bold text-red-200 leading-none font-serif w-8 shrink-0 text-center">{{ $loop->iteration }}</span>
    <a href="{{ route('site.post', $post->slug) }}" class="group">
        <h4 class="font-semibold text-sm leading-snug group-hover:text-brand transition-colors line-clamp-3">{{ $post->title }}</h4>
        <span class="text-[11px] text-gray-500 mt-1 block">{{ $post->publishDateShort() }}</span>
    </a>
</li>