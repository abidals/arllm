@extends('layouts.site')

@section('title', 'الأرشيف — '.config('app.name'))

@php
    $monthNames = [1=>'يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
@endphp

@section('content')
    <header class="mb-8 border-b-4 border-gray-900 pb-3">
        <h1 class="text-3xl font-bold">أرشيف الإصدارات</h1>
        <p class="text-gray-600 mt-2 font-naskh">تصفح إصدارات {{ config('app.name') }} حسب الشهر</p>
    </header>

    @if ($selected)
        {{-- مقالات شهر محدد --}}
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold">{{ $monthNames[$selectedMonth] }} {{ $selectedYear }}</h2>
            <a href="{{ route('site.archives') }}" class="text-sm font-semibold text-red-700 hover:underline">← كل الأشهر</a>
        </div>

        <div class="space-y-4 max-w-3xl">
            @forelse ($posts as $post)
                <a href="{{ route('site.post', $post->slug) }}" class="block bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        @if ($post->category)
                            <span class="text-red-700 font-bold">{{ $post->category->name }}</span>
                            <span aria-hidden="true">•</span>
                        @endif
                        <time>{{ $post->publishDateShort() }}</time>
                    </div>
                    <h3 class="font-bold text-lg mt-1.5 leading-snug hover:text-red-700">{{ $post->title }}</h3>
                    @if ($post->excerpt)
                        <p class="text-sm text-gray-600 mt-1.5 font-naskh line-clamp-2">{{ $post->excerpt }}</p>
                    @endif
                </a>
            @empty
                <p class="text-gray-500 py-10 text-center">لا توجد إصدارات في هذا الشهر.</p>
            @endforelse
        </div>
    @else
        {{-- شبكة الأشهر --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse ($groups as $group)
                @php [$year, $month] = explode('-', $group->month); @endphp
                <a href="{{ route('site.archives', ['month' => $group->month]) }}"
                   class="bg-white rounded-xl border border-gray-200 p-5 text-center hover:border-red-700 hover:shadow-md transition-all">
                    <div class="text-xs text-gray-500">{{ $year }}</div>
                    <div class="text-xl font-bold mt-1">{{ $monthNames[(int) $month] }}</div>
                    <div class="text-xs text-gray-500 mt-2">{{ $group->total }} إصدار</div>
                </a>
            @empty
                <p class="text-gray-500 col-span-4 py-10 text-center">لا يوجد أرشيف بعد.</p>
            @endforelse
        </div>
    @endif
@endsection