@extends('layouts.site')

@section('title', $title.' — '.config('app.name'))

@section('content')
    <article class="max-w-6xl mx-auto">
        {{-- غلاف العدد --}}
        <header class="text-center border-y-4 border-double border-gray-900 py-10 mb-10 relative">
            <div class="absolute top-4 right-4 text-[11px] font-bold tracking-wider text-red-700 border border-red-700 px-2 py-1 rounded-full">
                {{ $day->locale('ar')->isoFormat('dddd') }} · {{ config('app.name') }}
            </div>
            <p class="text-xs tracking-[0.3em] text-gray-500 font-semibold mb-3">إصدار يومي</p>
            <h1 class="text-4xl sm:text-6xl font-black tracking-tight">{!! $title !!}</h1>
            <p class="text-gray-500 text-sm mt-4">
                {{ $day->locale('ar')->isoFormat('D MMMM YYYY') }} —
                تجمعة سباق للمقالات المنشورة في هذا اليوم، بتحرير الغرفة الإخبارية.
            </p>
        </header>

        {{-- المقال الشبر --}}
        @if ($cover)
            <section class="mb-12">
                <a href="{{ route('site.post', $cover->slug) }}" class="group block relative rounded-2xl overflow-hidden">
                    <div class="aspect-[21/9] max-h-[440px] bg-gray-800">
                        @if ($cover->featured_image)
                            <img src="{{ Storage::url($cover->featured_image) }}" alt="{{ $cover->title }}" class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform">
                        @else
                            <div class="w-full h-full bg-gradient-to-bl from-gray-900 via-red-950 to-gray-900"></div>
                        @endif
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-transparent"></div>
                    <div class="absolute bottom-0 right-0 p-6 sm:p-10 max-w-3xl">
                        <span class="text-red-300 text-xs tracking-widest font-bold">قصة الغلاف</span>
                        <h2 class="text-2xl sm:text-4xl font-bold text-white mt-2 leading-tight group-hover:underline decoration-2 underline-offset-8">{{ $cover->title }}</h2>
                        @if ($cover->subtitle)<p class="text-gray-300 mt-3 font-naskh leading-relaxed">{{ $cover->subtitle }}</p>@endif
                        <div class="text-gray-400 text-xs mt-4">{{ $cover->author?->approvedName() }} · قراءة {{ $cover->reading_time }} دقائق</div>
                    </div>
                </a>
            </section>
        @endif

        {{-- الأقسام بقوائم تحريرية --}}
        <div class="space-y-12">
            @forelse ($sections as $sectionName => $posts)
                <section class="grid lg:grid-cols-[220px_1fr] gap-8 border-t border-gray-300 pt-8 first:border-t-0 first:pt-0">
                    <div class="lg:border-l lg:border-gray-300 lg:pl-6">
                        <h3 class="text-xl font-black border-b-4 border-red-700 inline-block pb-1">{{ $sectionName }}</h3>
                        <p class="text-xs text-gray-500 mt-2">{{ $posts->count() }} تحرير</p>
                    </div>
                    <ol class="space-y-5">
                        @foreach ($posts->take(6) as $post)
                            <li>
                                <a href="{{ route('site.post', $post->slug) }}" class="group grid sm:grid-cols-[1fr_auto] gap-2 items-baseline">
                                    <div>
                                        <h4 class="font-bold text-lg group-hover:text-red-700 transition-colors leading-snug">{{ $post->title }}</h4>
                                        <p class="text-sm text-gray-600 font-naskh line-clamp-1 mt-1">{{ $post->excerpt }}</p>
                                    </div>
                                    <div class="text-[11px] text-gray-500 whitespace-nowrap sm:text-left">{{ \Illuminate\Support\Str::of($post->content_md ?? '')->wordCount() }} كلمات</div>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @empty
                <p class="text-gray-500 py-16 text-center">لا توجد مقالات في هذا اليوم.</p>
            @endforelse
        </div>

        <div class="text-center mt-14 pt-8 border-t border-gray-200 text-sm">
            <a href="{{ route('site.archives') }}" class="text-red-700 font-bold hover:underline">تصفح أرشيف الأعداد ←</a>
        </div>
    </article>
@endsection