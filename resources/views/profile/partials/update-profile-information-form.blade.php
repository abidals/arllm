<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            بياناتي الشخصية
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            حدّث اسمك واسمك المعروض وصورتك وبريدك الإلكتروني ونبذتك.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    {{-- شريط الصورة الحالية — نموذج الإزالة خارج نموذج الحفظ --}}
    @if ($user->avatar)
        <div class="mt-4 bg-white border border-gray-200 rounded-xl p-3 flex items-center gap-3">
            <img src="{{ Storage::url($user->avatar) }}" alt="الصورة" class="w-12 h-12 rounded-full object-cover border">
            <span class="text-xs text-gray-500 flex-1">صورتك الحالية — الرفع الجديد يستبدلها.</span>
            <form method="POST" action="{{ route('profile.remove-avatar') }}"
                  onsubmit="return confirm('إزالة الصورة الشخصية وحذف ملفها؟')">
                @csrf
                <button class="text-xs text-red-700 border border-red-200 rounded px-3 py-1.5 font-semibold hover:bg-red-50">
                    إزالة الصورة
                </button>
            </form>
        </div>
    @endif

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="name" :value="__('اسم الدخول')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="display_name" :value="__('الاسم المعروض للمؤلف')" />
                <x-text-input id="display_name" name="display_name" type="text" class="mt-1 block w-full" :value="old('display_name', $user->display_name)" autocomplete="nickname" />
                <x-input-error class="mt-2" :messages="$errors->get('display_name')" />
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('البريد الإلكتروني')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('البريد غير موثق.') }}
                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900">
                            {{ __('أعد إرسال رابط التوثيق.') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('تم إرسال رابط توثيق جديد إلى بريدك.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="bio" :value="__('نبذة تظهر مع مقالاتك')" />
            <textarea id="bio" name="bio" rows="3" class="mt-1 block w-full border-gray-300 rounded-lg font-naskh" maxlength="1000">{{ old('bio', $user->bio) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <div>
            <x-input-label for="avatar" :value="__('صورة شخصية جديدة')" />
            <div class="flex items-center gap-4 mt-1">
                @if ($user->avatar)
                    <span class="text-[11px] text-gray-500">الحالية في الشريط أعلى النموذج.</span>
                @else
                    <span class="w-14 h-14 rounded-full bg-brand-soft text-brand flex items-center justify-center font-bold text-xl">{{ mb_substr(($user->display_name ?: $user->name), 0, 1) }}</span>
                @endif
                <input type="file" name="avatar" id="avatar" accept=".jpg,.jpeg,.png,.webp"
                       class="flex-1 text-xs border border-gray-300 rounded file:me-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-gray-900 file:text-white">
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            <p class="text-xs text-gray-500 mt-1">JPG / PNG / WebP — حتى 2MB.</p>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('حفظ') }}</x-primary-button>

            @if (session('status') === 'profile-updated' || session()->has('status'))
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-sm text-green-700 font-semibold"
                >{{ session('status') }}</p>
            @endif
        </div>
    </form>
</section>