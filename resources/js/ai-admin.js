/* مساعد الذكاء الاصطناعي داخل لوحة التحكم — التحرير عبر مكالمات محلية فقط */

(function () {
    'use strict';

    function bootPanel() {
        const panel = document.querySelector('[data-ai-panel]');
        if (!panel) return;

        const context = panel.dataset.aiContext || 'post';
        const result = panel.querySelector('[data-ai-result]');
        const status = panel.querySelector('[data-ai-status]');
        const buttons = panel.querySelectorAll('[data-ai-action]');
        const followUps = panel.querySelectorAll('[data-ai-insert], [data-ai-replace], [data-ai-excerpt], [data-ai-copy]');

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        let targetTextarea = null;

        // تتبع محرر Markdown الذي ركّز عليه المحرر (مقال واحد أو صفحات متعددة)
        function trackTargets() {
            const areas = document.querySelectorAll('textarea[name="content_md"]');
            if (!areas.length) return null;
            if (!targetTextarea || ![...areas].includes(targetTextarea)) {
                targetTextarea = areas.first();
            }
            return targetTextarea;
        }

        document.addEventListener('focusin', (e) => {
            if (e.target.matches('textarea[name="content_md"]')) {
                targetTextarea = e.target;
            }
        });

        function setStatus(text, type) {
            status.classList.remove('hidden');
            status.textContent = text;
            status.className = 'rounded-lg px-3 py-2 text-xs font-semibold ' + (
                type === 'error' ? 'bg-red-50 text-red-700 border border-red-200'
                    : type === 'ok' ? 'bg-green-50 text-green-800 border border-green-200'
                        : 'bg-blue-50 text-blue-800 border border-blue-200'
            );
        }

        function setPending(pending) {
            buttons.forEach((b) => { b.disabled = pending; });
            followUps.forEach((b) => { b.disabled = pending || !result.value; });
            if (pending) {
                setStatus('جارٍ التوليد… قد يستغرق ثوانٍ.', 'info');
            }
        }

        async function generate(action) {
            const textarea = trackTargets();

            if (!textarea || !textarea.value.trim()) {
                setStatus('اكتب محتوى أولًا في محرر Markdown ليُعاد صياغته.', 'error');
                return;
            }

            setPending(true);
            try {
                const response = await fetch('/admin/ai/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ action, text: textarea.value, context }),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    setStatus(data.error || 'تعذر التوليد.', 'error');
                    setPending(false);
                    return;
                }

                result.value = data.text || '';
                followUps.forEach((b) => { b.disabled = false; });
                setStatus('تم التوليد. راجع النص قبل الإدراج — المساعد قد يخطئ.', 'ok');
            } catch (err) {
                setStatus('فشل الاتصال بالخادم.', 'error');
            } finally {
                setPending(false);
            }
        }

        buttons.forEach((btn) => {
            btn.addEventListener('click', () => generate(btn.dataset.aiAction));
        });

        panel.querySelector('[data-ai-insert]').addEventListener('click', () => {
            const textarea = trackTargets();
            if (!textarea) return;

            textarea.value = textarea.value.trimEnd() + '\n\n' + result.value + '\n';
            textarea.dispatchEvent(new Event('input'));
            setStatus('أُدرج النص نهاية المحتوى.', 'ok');
        });

        panel.querySelector('[data-ai-replace]').addEventListener('click', () => {
            const textarea = trackTargets();
            if (!textarea || !confirm('استبدال المحتوى الحالي بالكامل؟')) return;

            textarea.value = result.value;
            textarea.dispatchEvent(new Event('input'));
            setStatus('تم استبدال المحتوى.', 'ok');
        });

        panel.querySelector('[data-ai-excerpt]').addEventListener('click', () => {
            const excerptField = document.querySelector('#excerpt, textarea[name="excerpt"], input[name="excerpt"]');
            if (!excerptField) {
                setStatus('لا يوجد حقل مقتطف في هذه الصفحة.', 'error');
                return;
            }

            excerptField.value = result.value.substring(0, 500);
            excerptField.dispatchEvent(new Event('input'));
            setStatus('نُقل النص إلى المقتطف.', 'ok');
        });

        panel.querySelector('[data-ai-copy]').addEventListener('click', async () => {
            await navigator.clipboard.writeText(result.value);
            setStatus('نُسخ النص.', 'ok');
        });

        followUps.forEach((b) => { b.disabled = true; });
    }

    bootPanel();
})();