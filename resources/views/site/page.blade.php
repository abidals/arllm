@extends('layouts.site')

@section('title', $page->title.' — '.config('app.name'))

@section('content')
    <article class="max-w-3xl mx-auto">
        <header class="border-b border-gray-200 pb-6 mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold">{{ $page->title }}</h1>
            @if ($page->subtitle)
                <p class="text-lg text-gray-600 font-naskh mt-3">{{ $page->subtitle }}</p>
            @endif
        </header>

        <div class="article-body">
            {!! $page->content_html !!}
        </div>
    </article>
@endsection