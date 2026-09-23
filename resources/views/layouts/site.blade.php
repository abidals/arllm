<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', \Illuminate\Support\Str::limit(\App\Models\Setting::get('site_description', 'صحافة رقمية عربية'), 160))">
    <link rel="canonical" content="@yield('canonical', url()->current())">
    <meta property="og:title" content="@yield('og_title', config('app.name'))">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.png'))">

    @stack('styles')

    {{-- خطوط Google العربية --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Noto+Naskh+Arabic:wght@400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">

    {{-- الشريط العلوي --}}
    <div class="bg-gray-900 text-gray-200 text-xs">
        <div class="max-w-7xl mx-auto px-4 py-1.5 flex items-center justify-between">
            <span class="hidden sm:block font-medium">{{ now()->locale('ar')->isoFormat('dddd، D MMMM YYYY') }}</span>
            <div class="flex items-center gap-4">
                <a href="{{ route('site.archives') }}" class="hover:text-white">الأرشيف</a>
                @if (auth()->check())
                    @if (auth()->user()->can('access-dashboard'))
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-white">لوحة التحكم</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button class="hover:text-white">خروج</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-white">دخول المحررين</a>
                @endif
            </div>
        </div>
    </div>

    {{-- الترويسة --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between py-3">
                <a href="{{ route('home') }}" class="flex items-baseline gap-2">
                    <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-950">
                        {{ config('app.name') }}
                    </h1>
                </a>

                <form role="search" action="{{ route('site.search') }}" method="GET" class="flex items-center gap-2">
                    <input type="search" name="q" value="{{ request()->query('q') }}"
                           placeholder="ابحث في الموقع…"
                           maxlength="100"
                           class="w-40 sm:w-64 border-gray-300 rounded-full text-sm px-4 py-1.5 focus:border-gray-900 focus:ring-gray-900">
                    <button class="text-gray-500 hover:text-gray-900" aria-label="بحث">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.34-4.34M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" />
                        </svg>
                    </button>
                </form>
            </div>

            <nav class="border-t border-gray-100 overflow-x-auto" aria-label="الأقسام">
                <ul class="flex items-center gap-6 py-2 text-sm font-semibold whitespace-nowrap">
                    @foreach (site_sections() as $section)
                        <li>
                            <a href="{{ route('site.category', $section['slug']) }}"
                               class="hover:text-red-700 {{ request()->is('section/'.$section['slug']) ? 'text-red-700' : '' }}">
                                {{ $section['name'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>
    </header>

    {{-- المحتوى --}}
    <main class="max-w-7xl mx-auto px-4 py-8 w-full flex-1">
        @if (session('status'))
            <div class="mb-6 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm font-medium">
                {{ session('status') }}
            </div>
        @endif
        @if (session('comment_status'))
            <div class="mb-6 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 text-sm font-medium">
                {{ session('comment_status') }}
            </div>
        @endif

        @yield('content')
    </main>

    {{-- التذييل --}}
    <footer class="bg-gray-900 text-gray-300 mt-12">
        <div class="max-w-7xl mx-auto px-4 py-10 grid gap-8 md:grid-cols-3">
            <div>
                <h2 class="text-2xl font-bold text-white mb-2">{{ config('app.name') }}</h2>
                <p class="text-sm leading-relaxed text-gray-400">
                    {{ \App\Models\Setting::get('site_tagline', 'صحافة رقمية بالعربية') }}
                </p>
            </div>
<div>
                <h3 class="font-semibold text-white mb-3 text-sm">الأقسام</h3>
                <ul class="space-y-1.5 text-sm">
                    @foreach (site_sections() as $section)
                        <li><a href="{{ route('site.category', $section['slug']) }}" class="hover:text-white">{{ $section['name'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h3 class="font-semibold text-white mb-3 text-sm">روابط</h3>
                <ul class="space-y-1.5 text-sm">
                    <li><a href="{{ route('site.archives') }}" class="hover:text-white">الأرشيف</a></li>
                    <li><a href="{{ route('site.search') }}" class="hover:text-white">البحث</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white">دخول المحررين</a></li>
                    @foreach (nav_pages() as $navPage)
                        <li><a href="{{ route('site.page', $navPage['slug']) }}" class="hover:text-white">{{ $navPage['title'] }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-800">
            <p class="max-w-7xl mx-auto px-4 py-4 text-xs text-gray-500">
                © {{ now()->year }} {{ config('app.name') }} — {{ \App\Models\Setting::get('footer_text', 'جميع الحقوق محفوظة') }}
            </p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>