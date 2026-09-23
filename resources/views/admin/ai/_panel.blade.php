@can('use-ai')
    <div class="bg-white rounded-xl shadow p-6 space-y-3" data-ai-panel data-ai-context="{{ $context ?? 'post' }}">
        <h3 class="font-bold border-b border-gray-200 pb-3 flex items-center justify-between">
            <span>مساعد الذكاء الاصطناعي</span>
            <span class="text-[10px] font-normal text-gray-400">مفتاح API محفوظ مشفّرًا على الخادم</span>
        </h3>

        <div class="grid grid-cols-2 gap-2">
            <button type="button" data-ai-action="improve" class="border border-gray-300 rounded-lg py-2 text-sm font-semibold hover:bg-gray-50">تحسين الصياغة</button>
            <button type="button" data-ai-action="excerpt" class="border border-gray-300 rounded-lg py-2 text-sm font-semibold hover:bg-gray-50">توليد المقتطف</button>
            <button type="button" data-ai-action="titles" class="border border-gray-300 rounded-lg py-2 text-sm font-semibold hover:bg-gray-50">اقتراح عناوين</button>
            <button type="button" data-ai-action="structure" class="border border-gray-300 rounded-lg py-2 text-sm font-semibold hover:bg-gray-50">إعادة هيكلة (عناصر)</button>
        </div>

        <div class="text-xs text-gray-500">يُرسل النص الحالي من محرر Markdown إلى المزوّد عبر الخادم.</div>

        <div class="hidden" data-ai-status></div>

        <textarea data-ai-result rows="7" placeholder="ستظهر النتيجة هنا…" class="w-full border-gray-300 rounded-lg font-mono text-sm font-naskh"></textarea>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <button type="button" data-ai-insert class="bg-gray-900 text-white rounded-lg py-2 text-xs font-semibold hover:bg-gray-700">إدراج بنهاية المحتوى</button>
            <button type="button" data-ai-replace class="border border-red-300 text-red-700 rounded-lg py-2 text-xs font-semibold hover:bg-red-50">استبدال المحتوى</button>
            <button type="button" data-ai-excerpt class="border border-gray-300 rounded-lg py-2 text-xs font-semibold hover:bg-gray-50">كالمقتطف</button>
            <button type="button" data-ai-copy class="border border-gray-300 rounded-lg py-2 text-xs font-semibold hover:bg-gray-50">نسخ</button>
        </div>
    </div>
@endcan
@stack('ai-scripts')
    @vite(['resources/js/ai-admin.js'])
@stack('ai-scripts-end')