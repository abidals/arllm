@extends('layouts.site')

@section('title', 'قسم '.$category->name.' — '.config('app.name'))
@section('meta_description', $category->description ?? ('أحدث مقالات قسم '.$category->name))

@section('content')
    <header class="mb-8 border-b-4 border-brand pb-3">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold">{{ $category->name }}</h1>
            <a href="{{ route('site.issue') }}" class="text-sm text-brand font-bold hover:underline">عدد اليوم ←</a>
        </div>
        @if ($category->description)
            <p class="text-gray-600 mt-2 font-naskh">{{ $category->description }}</p>
        @endif
    </header>

    {{-- الأقسام الفرعية --}}
    @if ($children->isNotEmpty())
        <div class="flex flex-wrap gap-2 mb-8">
            @foreach ($children as $child)
                @if ($child->is_active)
                    <a href="{{ route('site.category', $child->slug) }}"
                       class="border border-gray-300 hover:border-brand hover:text-brand text-gray-700 text-sm px-4 py-1.5 rounded-full font-semibold transition-colors">
                        {{ $child->name }}
                    </a>
                @endif
            @endforeach
        </div>
    @endif

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($posts as $post)
            @include('site.partials.post-card', ['post' => $post, 'showCategory' => false])
        @empty
            <p class="text-gray-500 col-span-3 py-12 text-center">لا توجد مقالات في هذا القسم بعد.</p>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $posts->links() }}
    </div>
@endsection