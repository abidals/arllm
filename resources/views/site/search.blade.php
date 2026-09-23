@extends('layouts.site')

@section('title', $q ? 'نتائج البحث: '.$q : 'البحث'.' — '.config('app.name'))

@section('content')
    <header class="mb-8">
        <h1 class="text-3xl font-bold">
            @if ($q)
                نتائج البحث عن «{{ $q }}»
            @else
                ابحث في {{ config('app.name') }}
            @endif
        </h1>
    </header>

    <form action="{{ route('site.search') }}" method="GET" class="flex gap-2 max-w-xl mb-10">
        <input type="search" name="q" value="{{ $q }}" placeholder="اكتب كلمة البحث…" maxlength="100" required
               class="flex-1 border-gray-300 rounded-lg px-4 py-2.5 focus:border-gray-900 focus:ring-gray-900">
        <button class="bg-gray-900 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-gray-700">بحث</button>
    </form>

    @if ($q !== '' && $posts->isNotEmpty())
        <p class="text-sm text-gray-600 mb-6">{{ $posts->total() }} نتيجة</p>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($posts as $post)
                @include('site.partials.post-card', ['post' => $post, 'showCategory' => true])
            @endforeach
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
    @elseif ($q !== '')
        <p class="text-gray-500 py-10 text-center">لا توجد نتائج مطابقة. جرّب كلمات أخرى.</p>
    @endif
@endsection