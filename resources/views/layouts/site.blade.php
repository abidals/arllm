<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $siteTagline = \App\Models\Setting::get('site_tagline', '');
        $tabTitle = View::hasSection('title')
            ? View::getSection('title')
            : site_name().($siteTagline !== '' ? ' | '.\Illuminate\Support\Str::limit($siteTagline, 45) : '');
    @endphp

    <title>{{ $tabTitle }}</title>
    <meta name="description" content="@yield('meta_description', \Illuminate\Support\Str::limit(\App\Models\Setting::get('site_description', 'صحافة رقمية عربية'), 160))">
    <link rel="canonical" content="@yield('canonical', url()->current())">
    <meta property="og:title" content="@yield('og_title', config('app.name'))">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.png'))">

    @php
        $headingFamily = \App\Models\Setting::get('font_heading_family', 'IBM Plex Sans Arabic');
        $bodyFamily = \App\Models\Setting::get('font_body_family', 'Noto Naskh Arabic');
        $fontFamilies = array_values(array_unique([$headingFamily, $bodyFamily, 'IBM Plex Sans Arabic', 'Noto Naskh Arabic']));
        $familiesQuery = implode('&family=', array_map(fn ($f) => str_replace(' ', '+', $f).':wght@400;500;600;700', $fontFamilies));
        $menuPosition = \App\Models\Setting::get('menu_position', 'right');
        $menuAlign = ['right' => 'justify-start', 'center' => 'justify-center', 'left' => 'justify-end'][$menuPosition] ?? 'justify-start';
    @endphp

    @stack('styles')

    {{-- خطوط Google العربية (خيار الإعدادات) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ $familiesQuery }}&display=swap" rel="stylesheet">

    @if ($fav = \App\Models\Setting::get('site_favicon'))
        <link rel="icon" href="{{ Storage::url($fav) }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/site-nav.js'])

    {{-- الثيم: الألوان القابلة للتحكم من اللوحة --}}
    <style>
        :root {
            --brand: {{ \App\Models\Setting::get('brand_color', '#b91c1c') }};
            --dark: {{ \App\Models\Setting::get('dark_color', '#111827') }};
        }
        @if ($headingFontPath = \App\Models\Setting::get('heading_font_path'))
            @font-face {
                font-family: 'CustomHeadingFont';
                src: url('{{ Storage::url($headingFontPath) }}') format('woff2');
                font-display: swap;
            }
            :root { --font-arabic-headline: 'CustomHeadingFont', sans-serif; }
        @endif
        @if ($bodyFontPath = \App\Models\Setting::get('body_font_path'))
            @font-face {
                font-family: 'CustomBodyFont';
                src: url('{{ Storage::url($bodyFontPath) }}') format('woff2');
                font-display: swap;
            }
            :root { --font-arabic-body: 'CustomBodyFont', serif; }
        @endif
    </style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">

{{-- الشريط العلوي --}}
    <div class="bg-theme-dark text-gray-200 text-xs">
        <div class="max-w-7xl mx-auto px-4 py-1.5 flex items-center justify-between">
            <span class="hidden sm:block font-medium">@if (\App\Models\Setting::get('header_show_date', '1') === '1'){{ arabic_date() }}@else&nbsp;@endif</span>
<div class="flex items-center gap-4">
                @if (\App\Models\Setting::get('header_show_search', '1') === '1')
                    <button type="button" data-search-open aria-label="فتح البحث" class="hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.34-4.34M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" />
                        </svg>
                    </button>
                @endif
                <a href="{{ route('site.issue') }}" class="font-bold text-brand hover:opacity-80">عدد اليوم</a>
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
    <header class="bg-white border-b border-gray-200 z-50 shadow-sm {{ \App\Models\Setting::get('header_sticky', '1') === '1' ? 'sticky top-0' : '' }}">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between py-3">
                <a href="{{ route('home') }}" class="flex flex-col justify-center min-w-0">
                    @if ($logo = \App\Models\Setting::get('site_logo'))
                        <img src="{{ Storage::url($logo) }}" alt="{{ site_name() }}" class="h-10 sm:h-12 w-auto object-contain">
                    @else
                        <span class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-950 leading-none">
                            {{ site_name() }}
                        </span>
                    @endif
                    @if ($siteTagline !== '')
                        <span class="text-[11px] sm:text-xs text-gray-500 font-medium mt-1.5">{{ $siteTagline }}</span>
                    @endif
                </a>

                <div class="flex-1"></div>
            </div>

            <nav class="border-t border-gray-100 site-nav" aria-label="الأقسام">
                <ul class="flex flex-wrap {{ $menuAlign }} items-start gap-x-6 gap-y-1 py-2 text-sm font-semibold min-w-max lg:min-w-0">
                    @foreach (site_sections() as $section)
                        @php
                            $active = request()->is('section/'.$section['slug'])
                                || collect($section['children'])->contains(fn ($ch) => request()->is('section/'.$ch['slug']));
                        @endphp
                        <li class="nav-top {{ $active ? 'is-active' : '' }}">
                            <a href="{{ route('site.category', $section['slug']) }}"
                               class="flex items-center gap-1 hover:text-brand {{ $active ? 'text-brand' : '' }}">
                                {{ $section['name'] }}
                                @if ($section['has_children'])
                                    <svg class="w-3 h-3 mt-0.5 opacity-60" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </a>

@if ($section['has_children'])
                                <div class="nav-sub" role="menu" dir="rtl">
                                    @foreach ($section['children'] as $child)
                                        <a href="{{ route('site.category', $child['slug']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700" role="menuitem">
                                            {{ $child['name'] }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
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
    <footer class="bg-theme-dark text-gray-300 mt-12">
        <div class="max-w-7xl mx-auto px-4 py-10 grid gap-8 md:grid-cols-3">
            <div>
                <h2 class="text-2xl font-bold text-white mb-2">{{ site_name() }}</h2>
                <p class="text-sm leading-relaxed text-gray-400">
                    {{ \App\Models\Setting::get('site_tagline', 'صحافة رقمية بالعربية') }}
                </p>
            </div>
            @if (\App\Models\Setting::get('footer_show_sections', '1') === '1')
                <div>
                    <h3 class="font-semibold text-white mb-3 text-sm">الأقسام</h3>
                    <ul class="space-y-1.5 text-sm">
                        @foreach (site_sections() as $section)
                            <li><a href="{{ route('site.category', $section['slug']) }}" class="hover:text-white">{{ $section['name'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div>
                @if (\App\Models\Setting::get('footer_show_pages', '1') === '1' && count(nav_pages()))
                    <h3 class="font-semibold text-white mb-3 text-sm">الصفحات</h3>
                    <ul class="space-y-1.5 text-sm">
                        @foreach (nav_pages() as $navPage)
                            <li><a href="{{ route('site.page', $navPage['slug']) }}" class="hover:text-white">{{ $navPage['title'] }}</a></li>
                        @endforeach
                    </ul>
                @endif

                @if (\App\Models\Setting::get('footer_show_links', '1') === '1')
                    <h3 class="font-semibold text-white mb-3 text-sm mt-4">روابط</h3>
                    <ul class="space-y-1.5 text-sm">
                        <li><a href="{{ route('site.archives') }}" class="hover:text-white">الأرشيف</a></li>
                        <li><a href="{{ route('site.issue') }}" class="hover:text-white">عدد اليوم</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white">دخول المحررين</a></li>
                    </ul>
                @endif
            </div>
        </div>
        <div class="border-t border-gray-800">
            <p class="max-w-7xl mx-auto px-4 py-4 text-xs text-gray-500">
                © {{ now()->year }} {{ site_name() }} — {{ \App\Models\Setting::get('footer_text', 'جميع الحقوق محفوظة') }}
            </p>
        </div>
    </footer>

    @stack('scripts')

    {{-- نافذة البحث الكاملة --}}
    <div id="search-modal" class="hidden fixed inset-0 z-[60] items-center justify-center p-4">
        <div data-search-backdrop class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold">ابحث في {{ site_name() }}</h2>
                <button type="button" data-search-close aria-label="إغلاق" class="text-gray-400 hover:text-gray-900 text-2xl leading-none w-9 h-9 rounded-full hover:bg-gray-100">×</button>
            </div>

            <form action="{{ route('site.search') }}" method="GET" class="flex gap-2">
                <input type="search" data-search-input name="q" maxlength="100" required placeholder="اكتب كلمة البحث ثم Enter…"
                       class="flex-1 border-gray-300 rounded-xl px-5 py-3.5 text-lg focus:border-gray-900 focus:ring-gray-900">
                <button class="btn-brand">بحث</button>
            </form>

            <p class="text-xs text-gray-500 mt-3">ستنتقل إلى صفحة النتائج الكاملة مع فلاتر الأقسام.</p>
        </div>
    </div>
</body>
</html>