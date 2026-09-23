@extends('layouts.site')

@section('title', 'قسم '.$category->name.' — '.config('app.name'))
@section('meta_description', $category->description ?? ('أحدث مقالات قسم '.$category->name))

@section('content')
    <header class="mb-8 border-b-4 border-red-700 pb-3">
        <h1 class="text-3xl font-bold">{{ $category->name }}</h1>
        @if ($category->description)
            <p class="text-gray-600 mt-2 font-naskh">{{ $category->description }}</p>
        @endif
    </header>

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