import $ from 'jquery';
import DataTable from 'datatables.net';

window.$ = window.jQuery = $;
window.DataTable = DataTable;

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

/* ---------------- Table & DataTables Helper Logic ---------------- */
function initTableBehaviors() {
    // 1. Master Checkbox Select All / Deselect All
    $(document).on('change', '[data-table-select-all]', function () {
        const $container = $(this).closest('.table-container');
        const isChecked = $(this).is(':checked');
        const $rows = $container.find('[data-table-select-row]');

        $rows.prop('checked', isChecked);
        $rows.each(function () {
            $(this).closest('tr').toggleClass('is-selected', isChecked);
        });

        updateTableBulkBar($container);
    });

    // 2. Row Checkbox Selection
    $(document).on('change', '[data-table-select-row]', function () {
        const $container = $(this).closest('.table-container');
        const $row = $(this).closest('tr');
        $row.toggleClass('is-selected', $(this).is(':checked'));

        const total = $container.find('[data-table-select-row]').length;
        const checked = $container.find('[data-table-select-row]:checked').length;
        const $master = $container.find('[data-table-select-all]');

        if (checked === 0) {
            $master.prop('checked', false).prop('indeterminate', false);
        } else if (checked === total) {
            $master.prop('checked', true).prop('indeterminate', false);
        } else {
            $master.prop('checked', false).prop('indeterminate', true);
        }

        updateTableBulkBar($container);
    });

    function updateTableBulkBar($container) {
        const checked = $container.find('[data-table-select-row]:checked').length;
        const $bulkBar = $container.find('[data-table-bulk-bar]');
        $bulkBar.find('.selected-count').text(checked);
        if (checked > 0) {
            $bulkBar.slideDown(150);
        } else {
            $bulkBar.slideUp(150);
        }
    }

    // 3. Quick Instant Search for Static Tables
    $(document).on('input', '.table-quick-search', function () {
        const query = $(this).val().toLowerCase().trim();
        const targetId = $(this).data('target');
        const $table = targetId ? $('#' + targetId) : $(this).closest('.table-container').find('.app-table');

        if (!$table.length) return;

        // If DataTable instance exists, delegate to DataTable search
        if ($.fn.DataTable && $.fn.DataTable.isDataTable($table)) {
            $table.DataTable().search(query).draw();
            return;
        }

        // Otherwise filter standard HTML rows
        $table.find('tbody tr').each(function () {
            const rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(query) !== -1);
        });
    });
}

/**
 * Standardized DataTable Initializer Helper
 * @param {string|jQuery} selector
 * @param {object} options
 */
function initDataTable(selector, options = {}) {
    if (!$.fn.DataTable) {
        console.warn('DataTables plugin is not loaded yet.');
        return null;
    }

    const defaultOptions = {
        responsive: true,
        processing: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'সকল (All)']],
        language: {
            search: 'খুঁজুন / Search:',
            searchPlaceholder: 'এখানে লিখুন...',
            lengthMenu: 'প্রতি পেজে _MENU_ টি রেকর্ড',
            info: 'মোট _TOTAL_ টির মধ্যে _START_ থেকে _END_ দেখানো হচ্ছে',
            infoEmpty: 'কোনো রেকর্ড নেই',
            infoFiltered: '(_MAX_ টি রেকর্ড থেকে ফিল্টার করা)',
            zeroRecords: 'কোনো ম্যাচিং রেকর্ড পাওয়া যায়নি',
            emptyTable: 'টেবিলে কোনো তথ্য নেই',
            paginate: {
                first: 'প্রথম',
                previous: 'পূর্ববর্তী',
                next: 'পরবর্তী',
                last: 'সর্বশেষ'
            }
        },
        dom: '<"table-toolbar"<"table-toolbar-start"l><"table-toolbar-end"f>>rt<"table-footer"<"table-pagination-info"i><"table-pagination"p>>'
    };

    const merged = $.extend(true, {}, defaultOptions, options);
    return $(selector).DataTable(merged);
}

/* ---------------- Expose Globals for Blade Templates ---------------- */
window.toast = toast;
window.toggleSidebar = toggleSidebar;
window.setLang = setLang;
window.setTheme = setTheme;
window.setMobilePreview = setMobilePreview;
window.openModal = openModal;
window.closeModal = closeModal;
window.printSection = printSection;
window.initDataTable = initDataTable;

$(function () {
    initTableBehaviors();
});

