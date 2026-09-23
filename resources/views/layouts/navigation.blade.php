<nav class="bg-gray-900 border-b border-gray-800">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center min-w-0">
                <!-- Logo -->
                <a href="{{ route('admin.dashboard') }}" class="text-white text-xl font-bold shrink-0">
                    {{ site_name() }} <span class="text-brand">· اللوحة</span>
                </a>

                <!-- بحث سريع في كل بيانات اللوحة -->
                <form action="{{ route('admin.search') }}" method="GET" class="hidden lg:flex items-center ms-4" role="search">
                    <input type="search" name="q" value="{{ request()->query('q') }}" maxlength="100"
                           placeholder="ابحث: مقال، صفحة، 2026-09-23…"
                           class="w-52 bg-gray-800 border border-gray-700 rounded-lg text-xs text-gray-200 placeholder-gray-500 py-1.5 px-3 focus:border-gray-500">
                </form>

                <!-- Navigation Links -->
                <div class="hidden sm:flex items-center gap-1 sm:ms-4">
                    <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-800' }}">
                        الرئيسية
                    </a>
                    <a href="{{ route('admin.posts.index') }}" class="px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.posts.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-800' }}">
                        المقالات
                    </a>
                    @can('manage-categories')
                        <a href="{{ route('admin.categories.index') }}" class="px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.categories.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-800' }}">
                            الأقسام
                        </a>
                    @endcan
                    <a href="{{ route('admin.comments.index') }}" class="px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.comments.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-800' }}">
                        التعليقات
                    </a>
                    @can('manage-pages')
                        <a href="{{ route('admin.pages.index') }}" class="px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.pages.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-800' }}">
                            الصفحات
                        </a>
                    @endcan
                    @can('manage-users')
                        <a href="{{ route('admin.users.index') }}" class="px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.users.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-800' }}">
                            المستخدمون
                        </a>
                    @endcan
                    @can('manage-settings')
                        <a href="{{ route('admin.settings.edit') }}" class="px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.settings.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-800' }}">
                            الإعدادات
                        </a>
                    @endcan
                    @can('manage-ai')
                        <a href="{{ route('admin.ai.index') }}" class="px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.ai.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-800' }}">
                            مساعد AI
                        </a>
                    @endcan
                </div>
            </div>

            <!-- قائمة المستخدم: بلا JavaScript (تعمل تحت CSP صارم) -->
            <details class="relative flex items-center" data-user-menu>
                <summary class="list-none cursor-pointer select-none inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-md text-gray-200 hover:text-white hover:bg-gray-800">
                    {{ auth()->user()->approvedName() }}
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </summary>

                <div class="absolute left-0 top-full mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50 overflow-hidden">
                    <a href="{{ route('profile.edit') }}" class="block w-full px-4 py-2.5 text-start text-sm text-gray-700 hover:bg-gray-100 transition">
                        الملف الشخصي
                    </a>
                    <a href="{{ route('home') }}" class="block w-full px-4 py-2.5 text-start text-sm text-gray-700 hover:bg-gray-100 transition">
                        الموقع العام
                    </a>
                    <form method="POST" action="{{ route('logout') }}" aria-label="تسجيل الخروج">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2.5 text-start text-sm text-red-700 hover:bg-red-50 transition">
                            تسجيل الخروج
                        </button>
                    </form>
                </div>
            </details>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <details class="sm:hidden border-t border-gray-800" data-mobile-menu>
        <summary class="block px-4 py-2.5 text-sm text-gray-300 cursor-pointer select-none hover:text-white">
            القائمة
        </summary>
        <div class="pb-3">
            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-white' : 'text-gray-300' }}">الرئيسية</a>
            <a href="{{ route('admin.posts.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('admin.posts.*') ? 'bg-gray-800 text-white' : 'text-gray-300' }}">المقالات</a>
            @can('manage-categories')
                <a href="{{ route('admin.categories.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('admin.categories.*') ? 'bg-gray-800 text-white' : 'text-gray-300' }}">الأقسام</a>
            @endcan
            <a href="{{ route('admin.comments.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('admin.comments.*') ? 'bg-gray-800 text-white' : 'text-gray-300' }}">التعليقات</a>
            @can('manage-pages')
                <a href="{{ route('admin.pages.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('admin.pages.*') ? 'bg-gray-800 text-white' : 'text-gray-300' }}">الصفحات</a>
            @endcan
            @can('manage-users')
                <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('admin.users.*') ? 'bg-gray-800 text-white' : 'text-gray-300' }}">المستخدمون</a>
            @endcan
            @can('manage-settings')
                <a href="{{ route('admin.settings.edit') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('admin.settings.*') ? 'bg-gray-800 text-white' : 'text-gray-300' }}">الإعدادات</a>
            @endcan
            @can('manage-ai')
                <a href="{{ route('admin.ai.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('admin.ai.*') ? 'bg-gray-800 text-white' : 'text-gray-300' }}">مساعد AI</a>
            @endcan
        </div>
        <div class="border-t border-gray-800 pt-3 pb-2">
            <div class="px-4 text-xs text-gray-400">
                {{ auth()->user()->approvedName() }} — {{ auth()->user()->email }}
            </div>
            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-300">الملف الشخصي</a>
            <a href="{{ route('home') }}" class="block px-4 py-2 text-sm text-gray-300">الموقع العام</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="block w-full text-start px-4 py-2 text-sm text-red-400">تسجيل الخروج</button>
            </form>
        </div>
    </details>
</nav>