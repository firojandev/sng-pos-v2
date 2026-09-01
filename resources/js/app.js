import $ from 'jquery';

window.$ = window.jQuery = $;

/* ---------------- Global AJAX Setup ---------------- */
$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});

/* ---------------- Toast Notification ---------------- */
let toastTimer = null;
function toast(bn, en) {
    const $toast = $('#toast');
    if (!$toast.length) return;
    const msg = $('body').hasClass('lang-en') ? en : bn;
    $toast.text(msg).addClass('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => $toast.removeClass('show'), 2200);
}

/* ---------------- Sidebar (Desktop collapse & Mobile drawer) ---------------- */
function toggleSidebar(forceState) {
    const isMobile = $(window).width() <= 1024;
    const $app = $('.app');
    const $sidebar = $('#sidebar');
    const $overlay = $('#overlay');

    if (isMobile) {
        const shouldOpen = typeof forceState === 'boolean' ? forceState : !$sidebar.hasClass('open');
        $sidebar.toggleClass('open', shouldOpen);
        $overlay.toggleClass('show', shouldOpen);
    } else {
        const shouldCollapse = typeof forceState === 'boolean' ? !forceState : !$app.hasClass('sidebar-collapsed');
        $app.toggleClass('sidebar-collapsed', shouldCollapse);
        try {
            localStorage.setItem('sidebar-collapsed', shouldCollapse ? '1' : '0');
        } catch (e) {}
    }
}

function initSidebarState() {
    try {
        const isCollapsed = localStorage.getItem('sidebar-collapsed') === '1';
        if (isCollapsed && $(window).width() > 1024) {
            $('.app').addClass('sidebar-collapsed');
        }
    } catch (e) {}
}

/* ---------------- Generic Modals & Drawers ---------------- */
function openModal(id) {
    $('#' + id).addClass('open');
}

function closeModal(id) {
    $('#' + id).removeClass('open');
}

/* ---------------- Print a Section ---------------- */
function printSection(id) {
    const $el = $('#' + id);
    if (!$el.length) return;
    $el.addClass('print-only');
    window.print();
    $el.removeClass('print-only');
}

/* ---------------- Theme (Light / Dark) ---------------- */
function setTheme(theme) {
    if (theme === 'light' || theme === 'dark') {
        $('html').attr('data-theme', theme);
        try { localStorage.setItem('theme', theme); } catch (e) {}
    } else {
        $('html').removeAttr('data-theme');
        try { localStorage.removeItem('theme'); } catch (e) {}
    }
    updateThemeButtons();
}

function updateThemeButtons() {
    let stored = null;
    try { stored = localStorage.getItem('theme'); } catch (e) {}
    const systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const effective = stored || (systemDark ? 'dark' : 'light');
    $('#theme-light').toggleClass('active', effective === 'light');
    $('#theme-dark').toggleClass('active', effective === 'dark');
}

/* ---------------- Mobile Preview Toggle ---------------- */
function setMobilePreview(enabled) {
    $('body').toggleClass('mobile-preview', enabled);
    try { localStorage.setItem('mobile-preview', enabled ? '1' : '0'); } catch (e) {}
}

function updateMobilePreviewToggle() {
    let stored = null;
    try { stored = localStorage.getItem('mobile-preview'); } catch (e) {}
    const enabled = stored === '1';
    $('body').toggleClass('mobile-preview', enabled);
    $('#mobile-preview-input').prop('checked', enabled);
}

/* ---------------- Language Switch ---------------- */
const placeholderMap = {
    'খুঁজুন...': 'Search...',
};

function setLang(lang) {
    $('body').toggleClass('lang-en', lang === 'en');
    $('#btn-bn').toggleClass('active', lang === 'bn');
    $('#btn-en').toggleClass('active', lang === 'en');
    $('input.bn-ph').each(function () {
        const original = $(this).attr('data-bn-ph');
        $(this).attr('placeholder', lang === 'en' ? (placeholderMap[original] || original) : original);
    });
    localStorage.setItem('lang', lang);
}

function initLang() {
    $('input.bn-ph').each(function () {
        $(this).attr('data-bn-ph', $(this).attr('placeholder'));
    });
    setLang(localStorage.getItem('lang') || 'bn');
}

/* ---------------- Keyboard Shortcuts & Listeners ---------------- */
$(function () {
    initSidebarState();
    updateThemeButtons();
    updateMobilePreviewToggle();
    initLang();

    $(document).on('keydown', function (e) {
        // Ctrl+K / Cmd+K Search Focus
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const $search = $('.top-search input');
            if ($search.length) {
                $search.trigger('focus').trigger('select');
            }
        }
        // Escape closes sidebars/drawers/modals
        if (e.key === 'Escape') {
            toggleSidebar(false);
            $('.drawer-backdrop.open, .modal-backdrop.open, .unit-modal-backdrop.open').removeClass('open');
        }
    });
});

/* ---------------- Expose Globals for Blade Templates ---------------- */
window.toast = toast;
window.toggleSidebar = toggleSidebar;
window.setLang = setLang;
window.setTheme = setTheme;
window.setMobilePreview = setMobilePreview;
window.openModal = openModal;
window.closeModal = closeModal;
window.printSection = printSection;
