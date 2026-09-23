/* قائمة الأقسام: فتح/إغلاق بالنقر (تعمل تحت CSP) */

(function () {
    'use strict';

    const topItems = document.querySelectorAll('.site-nav .nav-top');
    if (!topItems.length) return;

    function closeAll(except) {
        topItems.forEach((item) => {
            if (item !== except) item.classList.remove('is-open');
        });
    }

    topItems.forEach((item) => {
        const link = item.querySelector(':scope > a');
        const sub = item.querySelector(':scope > .nav-sub');
        if (!link || !sub) return;

        link.addEventListener('click', (e) => {
            if (!item.classList.contains('is-open')) {
                e.preventDefault();
                closeAll(item);
                item.classList.add('is-open');
            }
        });
    });

    // النقر خارج القائمة يغلقها، وEsc أيضًا
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.site-nav .nav-top')) closeAll(null);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeAll(null);
    });

    /* ============ نافذة البحث المنبثقة ============ */

    const modal = document.getElementById('search-modal');

    if (modal) {
        const searchInput = modal.querySelector('[data-search-input]');
        const openBtn = document.querySelector('[data-search-open]');
        const closeBtn = modal.querySelector('[data-search-close]');
        const backdrop = modal.querySelector('[data-search-backdrop]');

        const openModal = () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => searchInput && searchInput.focus(), 30);
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        openBtn && openBtn.addEventListener('click', openModal);
        closeBtn && closeBtn.addEventListener('click', closeModal);
        backdrop && backdrop.addEventListener('click', closeModal);
        document.addEventListener('keydown', (e) => e.key === 'Escape' && closeModal());
    }
})();