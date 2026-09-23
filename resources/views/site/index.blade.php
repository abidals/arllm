@extends('layouts.site')

@section('content')
    @include('site.partials.ticker')

    @if ($featured)
        {{-- الهيرو: غلاف + جانبية بأكثر من مقال مميز --}}
        <section class="mb-10 grid lg:grid-cols-[2.2fr_1fr] gap-5 items-stretch">
            <a href="{{ route('site.post', $featured->slug) }}" class="group block relative rounded-2xl overflow-hidden">
                <div class="aspect-[21/9] max-h-[420px] bg-gray-800">
                    @if ($featured->featured_image)
                        <img src="{{ Storage::url($featured->featured_image) }}" alt="{{ $featured->title }}"
                             class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-500">
                    @else
                        <div class="w-full h-full bg-gradient-to-bl from-gray-900 via-red-950 to-gray-900"></div>
                    @endif
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent"></div>
                <div class="absolute bottom-0 right-0 p-6 sm:p-10 max-w-3xl">
                    @if ($featured->category)
                        <span class="inline-block bg-brand text-white text-xs font-bold px-3 py-1 rounded-full mb-3">
                            {{ $featured->category->name }}
                        </span>
                    @endif
                    <h2 class="text-2xl sm:text-4xl font-bold text-white leading-tight group-hover:underline decoration-2 underline-offset-8">
                        {{ $featured->title }}
                    </h2>
                    @if ($featured->subtitle)
                        <p class="text-gray-200 mt-3 text-sm sm:text-lg font-naskh leading-relaxed line-clamp-2">{{ $featured->subtitle }}</p>
                    @endif
                    <div class="flex items-center gap-2 text-gray-300 text-xs sm:text-sm mt-4">
                        <span class="font-semibold">{{ $featured->author?->approvedName() }}</span>
                        <span aria-hidden="true">•</span>
                        <time datetime="{{ $featured->published_at->toIso8601String() }}">{{ $featured->publishDateShort() }}</time>
                    </div>
                </div>
            </a>

            {{-- المقالات المميزة الجانبية --}}
            @if ($heroSide->isNotEmpty())
                <div class="grid grid-cols-1 gap-5">
                    @foreach ($heroSide->take(2) as $side)
                        <a href="{{ route('site.post', $side->slug) }}" class="group relative rounded-2xl overflow-hidden bg-gray-800">
                            <div class="aspect-[16/7] lg:h-[201px]">
                                @if ($side->featured_image)
                                    <img src="{{ Storage::url($side->featured_image) }}" alt="{{ $side->title }}" class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform">
                                @else
                                    <div class="w-full h-full bg-gradient-to-bl from-gray-800 via-red-950/70 to-gray-900"></div>
                                @endif
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                            <div class="absolute bottom-0 right-0 p-4">
                                @if ($side->category)
                                    <span class="text-brand font-bold text-[10px] bg-white/90 px-2 py-0.5 rounded-full">{{ $side->category->name }}</span>
                                @endif
                                <h3 class="text-white font-bold text-base leading-snug mt-1.5 group-hover:underline decoration-2 underline-offset-4 line-clamp-2">
                                    {{ $side->title }}
                                </h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="hidden lg:block"></div>
            @endif
        </section>
    @endif

    {{-- شريط المميزات (أنواع إبراز إضافية) --}}
    @if ($featuredStrip->isNotEmpty())
        <section class="mb-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold border-b-4 border-brand inline-block pb-1">مميزة اليوم</h3>
                <a href="{{ route('site.issue') }}" class="text-sm font-semibold text-brand hover:underline">العدد الكامل ←</a>
            </div>
            <div class="grid sm:grid-cols-3 gap-5">
                @foreach ($featuredStrip as $post)
                    @include('site.partials.post-card', ['post' => $post, 'showCategory' => true])
                @endforeach
            </div>
        </section>
    @endif

    <div class="grid lg:grid-cols-[1fr_300px] gap-10">
        {{-- التدفق الرئيسي --}}
        <section>
            <h3 class="text-xl font-bold border-b-4 border-brand inline-block pb-1 mb-6">أجدد القصص</h3>
            <div class="grid sm:grid-cols-2 gap-6">
                @forelse ($posts as $post)
                    @include('site.partials.post-card', ['post' => $post, 'showCategory' => true])
                @empty
                    <p class="text-gray-500 col-span-2 py-12 text-center">لا توجد مقالات منشورة بعد.</p>
                @endforelse
            </div>

            @if (!empty($posts->first()) && $sponsored && $posts->count() > 7)
                {{-- مساحة إعلانية بين المقالات --}}
                <div class="my-8 rounded-xl border-2 border-dashed border-gray-300 bg-white p-5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <span class="text-xs font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded">
                            {{ \App\Models\Setting::get('sponsored_label', 'محتوى إعلاني') }}
                        </span>
                        <a href="{{ route('site.post', $sponsored->slug) }}" class="font-bold hover:text-brand">
                            {{ $sponsored->title }}
                        </a>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                </div>
            @endif
        </section>

        {{-- الشريط الجانبي --}}
        <aside class="space-y-8 lg:sticky lg:top-32 self-start">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-lg font-bold border-b-2 border-gray-900 inline-block pb-1 mb-3">الأكثر قراءة</h3>
                <ol>
                    @forelse ($mostRead as $post)
                        @include('site.partials.most-read-item', ['post' => $post])
                    @empty
                        <li class="text-sm text-gray-500 py-4">لا توجد بيانات بعد.</li>
                    @endforelse
                </ol>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5 text-sm">
                <h3 class="text-lg font-bold border-b-2 border-gray-900 inline-block pb-1 mb-3">الأقسام</h3>
                <ul class="space-y-2">
                    @foreach (site_sections() as $section)
                        <li>
                            <a href="{{ route('site.category', $section['slug']) }}" class="flex justify-between hover:text-brand">
                                <span>{{ $section['name'] }}</span>
                                <span class="text-gray-400">←</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>
    </div>
@endsection