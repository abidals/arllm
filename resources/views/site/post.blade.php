@extends('layouts.site')

@section('title', $post->title.' — '.config('app.name'))
@section('og_title', $post->title)
@section('og_type', 'article')
@section('meta_description', $post->excerpt ?? mb_substr(strip_tags($post->content_html), 0, 160))
@php ($ogType) @endphp

@section('content')
    <article class="max-w-4xl mx-auto">
        {{-- الترويسة --}}
        <header class="border-b border-gray-200 pb-8 mb-8">
            @if ($post->category)
                <a href="{{ route('site.category', $post->category->slug) }}"
                   class="text-red-700 font-bold text-sm uppercase tracking-wide hover:underline">
                    {{ $post->category->name }}
                </a>
            @endif
            <h1 class="text-3xl sm:text-5xl font-bold leading-tight mt-3">{{ $post->title }}</h1>
            @if ($post->subtitle)
                <p class="text-lg sm:text-xl text-gray-600 font-naskh leading-relaxed mt-4">{{ $post->subtitle }}</p>
            @endif

            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-500 mt-6">
                @if ($post->author)
                    <a href="{{ route('site.author', $post->author->id) }}" class="flex items-center gap-2 group">
                        <span class="w-9 h-9 rounded-full bg-red-100 text-red-800 flex items-center justify-center font-bold">
                            {{ mb_substr($post->author->approvedName(), 0, 1) }}
                        </span>
                        <span class="font-semibold text-gray-800 group-hover:text-red-700">{{ $post->author->approvedName() }}</span>
                    </a>
                @endif
                <span aria-hidden="true">•</span>
                <time datetime="{{ $post->published_at->toIso8601String() }}">{{ $post->publishDate() }}</time>
                <span aria-hidden="true">•</span>
                <span>{{ $post->reading_time }} دقائق قراءة</span>
                <span aria-hidden="true">•</span>
                <span>{{ number_format($post->views) }} مشاهدة</span>
            </div>
        </header>

        @if ($post->featured_image)
            <figure class="mb-8">
                <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="rounded-xl w-full">
                <figcaption class="text-xs text-gray-500 mt-2 text-center">{{ $post->title }}</figcaption>
            </figure>
        @endif

        {{-- المحتوى --}}
        <div class="article-body max-w-3xl mx-auto">
            {!! $post->content_html !!}
        </div>

        @if ($post->tags->count())
            <div class="flex flex-wrap gap-2 mt-10 pt-6 border-t border-gray-200">
                @foreach ($post->tags as $tag)
                    <span class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-3 py-1 rounded-full font-medium">
                        #{{ $tag->name }}
                    </span>
                @endforeach
            </div>
        @endif
    </article>

    {{-- مقالات مرتبطة --}}
    @if ($related->count())
        <section class="max-w-7xl mx-auto mt-16">
            <h2 class="text-xl font-bold border-b-4 border-red-700 inline-block pb-1 mb-6">مقالات مرتبطة</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($related as $r)
                    @include('site.partials.post-card', ['post' => $r, 'showCategory' => true])
                @endforeach
            </div>
        </section>
    @endif

    {{-- التعليقات --}}
    @if (\App\Models\Setting::get('comments_enabled', '1') === '1')
        <section class="max-w-3xl mx-auto mt-14">
            <h2 class="text-xl font-bold border-b-4 border-gray-900 inline-block pb-1 mb-6">
                التعليقات ({{ $comments->count() }})
            </h2>

            <div class="space-y-6">
                @forelse ($comments as $comment)
                    <div class="bg-white rounded-xl border border-gray-200 p-5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-bold">{{ $comment->name ?? 'زائر' }}</span>
                            <span class="text-gray-400 text-xs">{{ $comment->created_at->locale('ar')->diffForHumans() }}</span>
                        </div>
                        <p class="text-gray-700 mt-3 leading-relaxed font-naskh whitespace-pre-line">{{ $comment->body }}</p>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">لا توجد تعليقات بعد — كن أول المعلقين.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('site.comments.store', $post->slug) }}" class="bg-white rounded-xl border border-gray-200 p-6 mt-10">
                @csrf
                <h3 class="font-bold text-lg mb-4">أضف تعليقًا</h3>

                @guest
                    <div class="grid sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="c-name" class="block text-sm font-semibold mb-1">الاسم <span class="text-red-600">*</span></label>
                            <input id="c-name" type="text" name="name" required maxlength="80"
                                   class="w-full border-gray-300 rounded-lg text-sm focus:border-gray-900 focus:ring-gray-900">
                        </div>
                        <div>
                            <label for="c-email" class="block text-sm font-semibold mb-1">البريد الإلكتروني <span class="text-red-600">*</span></label>
                            <input id="c-email" type="email" name="email" required maxlength="120"
                                   class="w-full border-gray-300 rounded-lg text-sm focus:border-gray-900 focus:ring-gray-900">
                        </div>
                    </div>
                @endguest

                <div>
                    <label for="c-body" class="block text-sm font-semibold mb-1">تعليقك <span class="text-red-600">*</span></label>
                    <textarea id="c-body" name="body" rows="4" required maxLength="2000"
                              class="w-full border-gray-300 rounded-lg text-sm font-naskh focus:border-gray-900 focus:ring-gray-900"
                              placeholder="عبرتك رأيك…"></textarea>
                </div>

                <button type="submit" class="mt-4 bg-gray-900 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-gray-700 transition-colors">
                    إرسال التعليق
                </button>
                <p class="text-xs text-gray-500 mt-3">ستُنشر التعليقات بعد مراجعة المحرر. ممنوع الإساءة والسب.</p>
            </form>
        </section>
    @endif
@endsection