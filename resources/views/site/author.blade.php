@extends('layouts.site')

@section('title', 'مقالات '.$author->approvedName().' — '.config('app.name'))

@section('content')
    <header class="bg-white rounded-2xl border border-gray-200 p-8 mb-8 flex items-start gap-6">
        <span class="w-16 h-16 rounded-full bg-red-100 text-red-800 flex items-center justify-center font-bold text-2xl shrink-0">
            {{ mb_substr($author->approvedName(), 0, 1) }}
        </span>
        <div>
            <h1 class="text-2xl font-bold">{{ $author->approvedName() }}</h1>
            @if ($author->bio)
                <p class="text-gray-600 mt-2 font-naskh leading-relaxed">{{ $author->bio }}</p>
            @endif
            <p class="text-sm text-gray-500 mt-2">{{ $posts->total() }} مقال</p>
        </div>
    </header>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($posts as $post)
            @include('site.partials.post-card', ['post' => $post, 'showCategory' => true])
        @empty
            <p class="text-gray-500 col-span-3 py-12 text-center">لا توجد مقالات لهذا المؤلف بعد.</p>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $posts->links() }}
    </div>
@endsection