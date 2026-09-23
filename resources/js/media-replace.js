/* رفع ملف الاستبدال تلقائيًا بعد الاختيار */

(function () {
    'use strict';

    document.querySelectorAll('[data-replace-input]').forEach((input) => {
        input.addEventListener('change', () => {
            if (input.value) {
                input.closest('form').submit();
            }
        });
    });
})();