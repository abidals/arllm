@extends('layouts.site')

@section('title', 'الصفحة غير موجودة — '.site_name())

@section('content')
    <div class="max-w-xl mx-auto text-center py-16">
        <div class="text-7xl font-black text-gray-200 mb-4" aria-hidden="true">404</div>
        <h1 class="text-2xl sm:text-3xl font-bold mb-3">صفحة غير موجودة</h1>
        <p class="text-gray-600 leading-relaxed font-naskh mb-1">
            نعتذر! الصفحة التي تحاول الوصول إليها غير موجودة أو ربما حُذفت أو رابطها قديم.
        </p>
        <p class="text-sm text-gray-500 mb-8">
            يمكنك العودة إلى الصفحة الرئيسية أو البحث أو تصفح أرشيف الأعداد.
        </p>

        <div class="flex flex-wrap gap-3 justify-center">
            <a href="{{ route('home') }}" class="btn-brand">← العودة إلى الرئيسية</a>
            <a href="{{ route('site.search') }}" class="border border-gray-300 rounded-lg px-5 py-2.5 text-sm font-semibold hover:bg-gray-50">🔍 البحث</a>
            <a href="{{ route('site.archives') }}" class="border border-gray-300 rounded-lg px-5 py-2.5 text-sm font-semibold hover:bg-gray-50">أرشيف الأعداد</a>
        </div>
    </div>
@endsection