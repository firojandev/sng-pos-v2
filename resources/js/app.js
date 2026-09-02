import $ from 'jquery';
import DataTable from 'datatables.net';
import Swal from 'sweetalert2';
import { createIcons, icons } from 'lucide';

window.$ = window.jQuery = $;
window.DataTable = DataTable;
window.Swal = Swal;
window.lucide = { createIcons, icons };
window.createIcons = (options = {}) => createIcons({ icons, ...options });



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
    $('#theme-toggle').prop('checked', effective === 'dark');
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
    $('html').toggleClass('lang-en', lang === 'en');
    $('body').toggleClass('lang-en', lang === 'en');
    $('#btn-bn').toggleClass('active', lang === 'bn');
    $('#btn-en').toggleClass('active', lang === 'en');
    $('#lang-toggle').prop('checked', lang === 'en');
    $('input.bn-ph').each(function () {
        const original = $(this).attr('data-bn-ph');
        $(this).attr('placeholder', lang === 'en' ? (placeholderMap[original] || original) : original);
    });
    $('[data-placeholder-en]').each(function () {
        const bn = $(this).attr('data-placeholder-bn') || $(this).attr('placeholder');
        const en = $(this).attr('data-placeholder-en');
        $(this).attr('placeholder', lang === 'en' ? en : bn);
    });
    $('.dataTables_filter input, .dt-search input').attr('placeholder', lang === 'en' ? 'Search...' : 'এখানে লিখুন...');
    localStorage.setItem('lang', lang);
}

function initLang() {
    $('input.bn-ph').each(function () {
        $(this).attr('data-bn-ph', $(this).attr('placeholder'));
    });
    $('[data-placeholder-en]').each(function () {
        if (!$(this).attr('data-placeholder-bn')) {
            $(this).attr('data-placeholder-bn', $(this).attr('placeholder'));
        }
    });
    const currentLang = localStorage.getItem('lang') || 'bn';
    $('#lang-toggle').prop('checked', currentLang === 'en');
    setLang(currentLang);
}

/* ---------------- Keyboard Shortcuts & Listeners ---------------- */
$(function () {
    initSidebarState();
    updateThemeButtons();
    updateMobilePreviewToggle();
    initLang();

    // Theme & Language Toggle Switch Listeners
    $(document).on('change', '#theme-toggle', function () {
        setTheme($(this).is(':checked') ? 'dark' : 'light');
    });

    $(document).on('change', '#lang-toggle', function () {
        setLang($(this).is(':checked') ? 'en' : 'bn');
    });

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

/* ---------------- Button Group & Segmented Controls ---------------- */
function initButtonGroupBehaviors() {
    $(document).on('click', '.btn-group-segmented .btn, .btn-group [data-btn-group-item]', function (e) {
        const $btn = $(this);
        const $group = $btn.closest('.btn-group, .btn-group-segmented, .btn-group-vertical');

        if ($btn.hasClass('disabled') || $btn.prop('disabled') || $btn.attr('aria-disabled') === 'true') {
            e.preventDefault();
            return;
        }

        const $radio = $btn.find('input[type="radio"]');
        if ($radio.length && !$radio.is(':disabled')) {
            $radio.prop('checked', true).trigger('change');
            $group.find('.btn').removeClass('active');
            $btn.addClass('active');
            return;
        }

        if ($group.hasClass('btn-group-segmented') || $btn.is('[data-btn-group-item]')) {
            $group.find('.btn').removeClass('active').removeAttr('aria-pressed');
            $btn.addClass('active').attr('aria-pressed', 'true');
            $group.trigger('change', [$btn.data('value') || $btn.text().trim()]);
        }
    });

    $(document).on('change', '.btn-group input[type="radio"], .btn-group-segmented input[type="radio"]', function () {
        const $group = $(this).closest('.btn-group, .btn-group-segmented, .btn-group-vertical');
        $group.find('.btn').removeClass('active');
        $(this).closest('.btn').addClass('active');
    });
}

/* ---------------- SweetAlert2 Confirmation Helpers ---------------- */
function confirmDelete(options = {}) {
    const isEn = $('body').hasClass('lang-en') || $('html').hasClass('lang-en');
    const isDark = $('html').attr('data-theme') === 'dark' || (!document.documentElement.hasAttribute('data-theme') && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);

    const defaultTitle = isEn ? 'Are you sure?' : 'আপনি কি নিশ্চিত?';
    const defaultText = isEn
        ? 'This item will be permanently deleted!'
        : 'এই তথ্যটি স্থায়ীভাবে মুছে ফেলা হবে!';
    const defaultConfirmText = isEn ? 'Yes, Delete' : 'হ্যাঁ, মুছে ফেলুন';
    const defaultCancelText = isEn ? 'Cancel' : 'বাতিল';

    return Swal.fire({
        title: options.title || defaultTitle,
        text: options.text || defaultText,
        icon: options.icon || 'warning',
        showCancelButton: true,
        confirmButtonColor: '#E11D48',
        cancelButtonColor: isDark ? '#334155' : '#64748B',
        confirmButtonText: options.confirmButtonText || defaultConfirmText,
        cancelButtonText: options.cancelButtonText || defaultCancelText,
        reverseButtons: true,
        background: isDark ? '#111827' : '#FFFFFF',
        color: isDark ? '#F8FAFC' : '#0F172A',
        customClass: {
            popup: 'app-swal-popup'
        }
    });
}

function initDeleteConfirmBehaviors() {
    $(document).on('submit', '.delete-plan-form, .delete-form, [data-confirm-delete]', function (e) {
        const $form = $(this);
        if ($form.data('confirmed')) {
            return true;
        }

        e.preventDefault();

        const isEn = $('body').hasClass('lang-en') || $('html').hasClass('lang-en');
        const isPlan = $form.hasClass('delete-plan-form');
        const customTitle = $form.data('title');
        const customText = $form.data('text');

        const title = customTitle || (isPlan
            ? (isEn ? 'Delete Plan?' : 'প্ল্যান মুছে ফেলতে চান?')
            : (isEn ? 'Are you sure?' : 'আপনি কি নিশ্চিত?'));

        const text = customText || (isPlan
            ? (isEn ? 'Are you sure you want to delete this plan? This action cannot be undone.' : 'এই প্ল্যানটি চিরতরে মুছে ফেলা হবে। আপনি কি নিশ্চিত?')
            : (isEn ? 'This record will be permanently deleted.' : 'এই তথ্যটি স্থায়ীভাবে মুছে ফেলা হবে।'));

        confirmDelete({
            title: title,
            text: text,
            confirmButtonText: isEn ? 'Yes, Delete' : 'হ্যাঁ, মুছুন',
            cancelButtonText: isEn ? 'Cancel' : 'বাতিল'
        }).then((result) => {
            if (result.isConfirmed) {
                $form.data('confirmed', true);
                $form.trigger('submit');
            }
        });
    });
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
window.initButtonGroupBehaviors = initButtonGroupBehaviors;
window.confirmDelete = confirmDelete;
window.initDeleteConfirmBehaviors = initDeleteConfirmBehaviors;

$(function () {
    initTableBehaviors();
    initButtonGroupBehaviors();
    initDeleteConfirmBehaviors();

    // Initialize Lucide Icons globally
    createIcons({ icons });

    // Re-initialize Lucide Icons on dynamic DOM modifications
    $(document).on('draw.dt ajaxComplete shown.bs.modal', function () {
        createIcons({ icons });
    });
});




