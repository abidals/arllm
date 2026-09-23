/* ترتيب ونقل الأقسام بالسحب والإفلات (HTML5 Drag & Drop) */

(function () {
    'use strict';

    const tree = document.querySelector('[data-cats-tree]');
    if (!tree) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const messageBox = document.getElementById('drag-message');
    if (!messageBox) {
        console.warn('drag-message container missing');
    }

    function message(text, isError) {
        if (!messageBox) return alert(text);
        messageBox.classList.remove('hidden');
        messageBox.className = 'mb-4 rounded-lg px-4 py-2.5 text-sm font-semibold ' + (isError
            ? 'bg-red-50 text-red-700 border border-red-200'
            : 'bg-green-50 text-green-800 border border-green-200');
        messageBox.textContent = text;
        if (!isError) setTimeout(() => messageBox.classList.add('hidden'), 2500);
    }

    function asInt(v) { return parseInt(v, 10) || null; }

    // حماية: السحب يبدأ من مقبض ⠿ وليس من الحقول
    tree.querySelectorAll('[data-cat-row]').forEach((row) => {
        row.addEventListener('dragstart', (e) => {
            if (!row.getAttribute('draggable') || row.getAttribute('draggable') === 'false') {
                e.preventDefault();
                return;
            }
            e.dataTransfer.setData('text/plain', String(row.dataset.id));
            e.dataTransfer.effectAllowed = 'move';
            row.style.opacity = '0.5';
        });
        row.addEventListener('dragend', () => { row.style.opacity = '1'; });
    });

    tree.querySelectorAll('form[data-no-drag]').forEach((form) => {
        form.addEventListener('mousedown', (e) => {
            if (e.target.matches('input, select, button, textarea, option, option *, [contenteditable]')) {
                const row = form.closest('[data-cat-row]');
                if (row) row.setAttribute('draggable', 'false');
            }
        });
    });
    tree.addEventListener('mouseup', () => {
        tree.querySelectorAll('[data-cat-row][draggable="false"]').forEach((row) => row.setAttribute('draggable', 'true'));
    });

    tree.querySelectorAll('[data-cats-list]').forEach((list) => {
        list.addEventListener('dragstart', (e) => {
            e.stopPropagation();
        });

        list.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.stopPropagation();
            list.classList.add('ring-2', 'ring-blue-300');
        });
        list.addEventListener('dragleave', () => list.classList.remove('ring-2', 'ring-blue-300'));

        list.addEventListener('drop', async (e) => {
            // القائمة الأعمق تُستهلك الحدث أولًا
            e.preventDefault();
            e.stopPropagation();
            list.classList.remove('ring-2', 'ring-blue-300');

            const id = parseInt(e.dataTransfer.getData('text/plain'), 10);
            const row = tree.querySelector('[data-cat-row][data-id="' + id + '"]');
            if (!row) return;

            const listParent = list.dataset.parent === 'top' ? null : asInt(list.dataset.parent);
            const rowId = asInt(row.dataset.id);

            if (listParent === rowId) {
                return;
            }

            // وضيعة الإسقاط: قبل العنصر الموجود تحته إن كان الإسقاط في نصفه الأول
            const afterElement = getDragAfterElement(list, e.clientY);
            if (row.parentElement !== list) {
                list.insertBefore(row, afterElement);
            }

            const items = [];
            tree.querySelectorAll('[data-cats-list]').forEach((l) => {
                const parentId = l.dataset.parent === 'top' ? null : asInt(l.dataset.parent);
                l.querySelectorAll(':scope > [data-cat-row]').forEach((r, index) => {
                    items.push({
                        id: asInt(r.dataset.id),
                        parent_id: parentId,
                        sort_order: index,
                    });
                });
            });

            try {
                const response = await fetch('/admin/categories/reorder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        Accept: 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ items }),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    message(data.error || 'تعذر حفظ الترتيب.', true);
                    location.reload();
                    return;
                }

                message('تم حفظ الترتيب بنجاح.');
            } catch (err) {
                message('فشل الاتصال بالخادم.', true);
            }
        });
    });

    function getDragAfterElement(container, y) {
        const rows = [...container.querySelectorAll(':scope > [data-cat-row]')];

        return rows.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;

            if (offset < 0 && offset > closest.offset) {
                return { offset, element: child };
            }

            return closest;
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
})();