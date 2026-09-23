<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            بوابة الذكاء الاصطناعي
            @if ($aiEnabled)
                <span class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-1 rounded-full ms-2">مفعّلة</span>
            @else
                <span class="bg-gray-200 text-gray-600 text-xs font-bold px-2.5 py-1 rounded-full ms-2">معطّلة — فعّلها من الإعدادات</span>
            @endif
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 grid lg:grid-cols-[400px_1fr] gap-6 items-start">
        {{-- إضافة مزوّد --}}
        <form method="POST" action="{{ route('admin.ai.providers.store') }}" class="bg-white rounded-xl shadow p-6 space-y-4">
            @csrf
            <h3 class="font-bold border-b border-gray-200 pb-3">مزوّد جديد</h3>

            <div>
                <label class="block text-sm font-bold mb-1.5">الاسم الظاهر <span class="text-red-600">*</span></label>
                <input name="label" required maxlength="100" placeholder="مثال: OpenAI GPT" class="w-full border-gray-300 rounded-lg">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-bold mb-1.5">النوع</label>
                    <select name="driver" class="w-full border-gray-300 rounded-lg bg-white">
                        <option value="openai-compatible">OpenAI-compatible (OpenAI/DeepSeek/Groq/Ollama…)</option>
                        <option value="anthropic">Anthropic (Claude)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1.5">النموذج</label>
                    <input name="model" required maxlength="100" placeholder="gpt-4o-mini" class="w-full border-gray-300 rounded-lg">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold mb-1.5">Base URL <span class="text-red-600">*</span></label>
                <input name="base_url" required placeholder="https://api.openai.com" class="w-full border-gray-300 rounded-lg" dir="ltr">
                <p class="text-xs text-gray-500 mt-1">HTTPS إلزامي. محليًا: https://localhost:11434 (Ollama).</p>
            </div>

            <div>
                <label class="block text-sm font-bold mb-1.5">مفتاح API <span class="text-red-600">*</span></label>
                <input name="api_key" type="password" required minlength="8" autocomplete="new-password" class="w-full border-gray-300 rounded-lg" dir="ltr">
                <p class="text-xs text-gray-500 mt-1">يُخزّن مشفّرًا AES-256 ولا يظهر أبدًا بعد الحفظ.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-bold mb-1.5">أقصى توكنات لكل استدعاء</label>
                    <input name="max_tokens" type="number" min="128" max="8192" value="1024" class="w-full border-gray-300 rounded-lg">
                </div>
                <label class="flex items-center gap-2 text-sm mt-6">
                    <input type="hidden" name="is_default" value="0">
                    <input type="checkbox" name="is_default" value="1" checked class="rounded border-gray-300 text-red-700">
                    افتراضي للاستخدام
                </label>
            </div>

            <button class="w-full bg-red-700 hover:bg-red-800 text-white font-semibold py-2.5 rounded-lg">إضافة المزوّد</button>
            @error('api_key') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
            @error('base_url') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
        </form>

        <div class="space-y-4">
            @if (session('status'))
                <div class="rounded bg-green-50 border border-green-200 text-green-800 px-4 py-2.5 text-sm">{{ session('status') }}</div>
            @endif

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 text-sm">
                <h3 class="font-bold text-blue-900 mb-2">كيف يعمل المساعد؟</h3>
                <ul class="text-blue-800 space-y-1.5 leading-relaxed list-disc ps-5">
                    <li>الاستدعاء يمر عبر خادمك فقط — المتصفح لا يرى مفتاح API مطلقًا.</li>
                    <li>يوفر: تحسين الصياغة، المقتطف، عناوين، إعادة هيكلة (عناصر: جداول/قوائم/عناوين).</li>
                    <li>معدل الاستخدام: 12 طلبًا/دقيقة لكل مستخدم (حماية من الإساءة والتكلفة).</li>
                </ul>
            </div>

            @forelse ($providers as $provider)
                <div class="bg-white rounded-xl shadow p-5 {{ $provider->is_default ? 'border-2 border-green-500' : '' }}">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h4 class="font-bold inline-flex items-center gap-2">
                                {{ $provider->label }}
                                @if ($provider->is_default)
                                    <span class="text-[11px] bg-green-100 text-green-800 font-bold px-2 py-0.5 rounded-full">افتراضي</span>
                                @endif
                                @unless ($provider->is_active)
                                    <span class="text-[11px] bg-gray-100 text-gray-600 font-bold px-2 py-0.5 rounded-full">معطّل</span>
                                @endunless
                            </h4>
                            <p class="text-xs text-gray-500 mt-1" dir="ltr">
                                {{ $provider->driver }} · {{ $provider->model }} · {{ $provider->base_url }}
                            </p>
                            <p class="text-xs text-gray-600 mt-1">
                                المفتاح: <code class="bg-gray-100 rounded px-1.5 py-0.5" dir="ltr">{{ $provider->maskedKey() }}</code>
                                <span class="text-gray-400">(مشفّر AES-256)</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            @unless ($provider->is_default)
                                <form method="POST" action="{{ route('admin.ai.providers.default', $provider) }}">
                                    @csrf
                                    <button class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg">تعيين افتراضي</button>
                                </form>
                            @endunless
                            <form method="POST" action="{{ route('admin.ai.providers.destroy', $provider) }}"
                                  onsubmit="return confirm('حذف المزوّد ومفتاحه المشفّر؟')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-700 hover:underline text-xs font-medium">حذف</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow p-12 text-center text-gray-500">
                    لا يوجد مزوّد بعد. أضف مزوّدًا من اليمين لبدء استخدام المساعد.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>