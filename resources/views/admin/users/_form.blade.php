{{-- نموذج مستخدم جديد/تعديل — المتغيرات: editingUser (اختياري) ، roles --}}
@php $editing = isset($editingUser); @endphp

<form method="POST" action="{{ $editing ? route('admin.users.update', $editingUser) : route('admin.users.store') }}" class="max-w-2xl space-y-5">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="bg-white rounded-xl shadow p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label for="name" class="block text-sm font-bold mb-1.5">اسم الدخول <span class="text-red-600">*</span></label>
                <input id="name" name="name" required maxlength="120" value="{{ old('name', $editingUser->name ?? '') }}"
                       class="w-full border-gray-300 rounded-lg">
            </div>
            <div>
                <label for="display_name" class="block text-sm font-bold mb-1.5">الاسم المعروض</label>
                <input id="display_name" name="display_name" maxlength="120" value="{{ old('display_name', $editingUser->display_name ?? '') }}"
                       class="w-full border-gray-300 rounded-lg">
            </div>
        </div>

        <div>
            <label for="email" class="block text-sm font-bold mb-1.5">البريد الإلكتروني <span class="text-red-600">*</span></label>
            <input id="email" name="email" type="email" required maxlength="150" value="{{ old('email', $editingUser->email ?? '') }}"
                   class="w-full border-gray-300 rounded-lg" dir="ltr">
        </div>

        <div>
            <label for="password" class="block text-sm font-bold mb-1.5">
                كلمة المرور @if ($editing) <span class="text-gray-400 font-normal">(اتركها فارغة للإبقاء)</span> @endif
                @unless ($editing) <span class="text-red-600">*</span> @endunless
            </label>
            <input id="password" name="password" type="password" {{ $editing ? '' : 'required' }} minlength="10" autocomplete="new-password"
                   class="w-full border-gray-300 rounded-lg" dir="ltr">
            <p class="text-xs text-gray-500 mt-1">10 خانات على الأقل.</p>
            @error('password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="bio" class="block text-sm font-bold mb-1.5">نبذة المؤلف</label>
            <textarea id="bio" name="bio" rows="3" maxlength="1000"
                      class="w-full border-gray-300 rounded-lg font-naskh">{{ old('bio', $editingUser->bio ?? '') }}</textarea>
        </div>

        <div>
            <label for="role" class="block text-sm font-bold mb-1.5">الدور <span class="text-red-600">*</span></label>
            <select id="role" name="role" required class="w-full border-gray-300 rounded-lg bg-white">
                <option value="author" @selected(old('role', $editingUser->roles->first()?->name ?? 'author') === 'author')>كاتب — مقالاته فقط</option>
                <option value="editor" @selected(old('role', $editingUser->roles->first()?->name ?? '') === 'editor')>محرر — نشر كل المقالات</option>
                @if (!$editing)
                    <option value="admin" @selected(old('role') === 'admin')>مدير — كل الصلاحيات</option>
                @elseif ($editingUser->roles->first()?->name === 'admin')
                    <option selected>ادمين</option>
                @endif
            </select>
            @if ($editing && $editingUser->id === auth()->id())
                <p class="text-xs text-gray-500 mt-1">لا يمكنك تغيير دورك الخاص.</p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-bold mb-1.5">الصورة الشخصية</label>
            @if ($editing && $editingUser->avatar)
                <div class="flex items-center gap-3 mb-2">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($editingUser->avatar) }}" alt="" class="w-14 h-14 rounded-full object-cover border">
                    <form method="POST" action="{{ route('admin.users.remove-avatar', $editingUser) }}"
                          onsubmit="return confirm('إزالة صورة المستخدم وحذف ملفها؟')">
                        @csrf
                        <button class="text-xs text-red-700 border border-red-200 rounded px-3 py-1.5 font-semibold hover:bg-red-50">
                            إزالة الصورة
                        </button>
                    </form>
                </div>
            @endif
            <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp"
                   class="block w-full text-xs border border-gray-300 rounded file:me-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-gray-900 file:text-white">
            @error('avatar') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="flex gap-3">
        <button class="bg-red-700 hover:bg-red-800 text-white font-semibold px-6 py-2.5 rounded-lg">
            {{ $editing ? 'تحديث المستخدم' : 'إنشاء المستخدم' }}
        </button>
        <a href="{{ route('admin.users.index') }}" class="border border-gray-300 rounded-lg px-6 py-2.5 font-semibold hover:bg-gray-50">إلغاء</a>
    </div>
</form>