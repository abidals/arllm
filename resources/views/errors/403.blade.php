@extends('layouts.site')

@section('title', 'غير مصرّح — '.site_name())

@section('content')
    <div class="max-w-xl mx-auto text-center py-16">
        <div class="text-7xl font-black text-gray-200 mb-4" aria-hidden="true">403</div>
        <h1 class="text-2xl sm:text-3xl font-bold mb-3">لا تملك صلاحية الوصول</h1>
        <p class="text-gray-600 leading-relaxed font-naskh mb-1">
            عذرًا، هذه الصفحة مخص لحسابات ذات صلاحيات أخص، أو أن جلستك منتهية.
        </p>
        <p class="text-sm text-gray-500 mb-8">
            جرّب تسجيل الدخول من جديد أو الرجوع للصفحة الرئيسية.
        </p>

        <div class="flex flex-wrap gap-3 justify-center">
            <a href="{{ route('home') }}" class="btn-brand">← الرئيسية</a>
            <a href="{{ route('login') }}" class="border border-gray-300 rounded-lg px-5 py-2.5 text-sm font-semibold hover:bg-gray-50">تسجيل الدخول</a>
        </div>
    </div>
@endsection