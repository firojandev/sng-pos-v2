import $ from 'jquery';
import DataTable from 'datatables.net';
import Swal from 'sweetalert2';
import { createIcons, icons } from 'lucide';

const safeCreateIcons = (options = {}) => createIcons({ icons, ...options });

window.$ = window.jQuery = $;
window.DataTable = DataTable;
window.Swal = Swal;
window.lucide = { createIcons: safeCreateIcons, icons };
window.createIcons = safeCreateIcons;



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
    const isDark = effective === 'dark';
    $('#theme-light').toggleClass('active', !isDark);
    $('#theme-dark').toggleClass('active', isDark);
    $('.switch-opt-light').toggleClass('active', !isDark);
    $('.switch-opt-dark').toggleClass('active', isDark);
    $('#theme-toggle').prop('checked', isDark);
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

const commonSelectPhrases = {
    '-- নির্বাচন করুন --': '-- Select --',
    '-- নির্বাচন করুন (ঐচ্ছিক) --': '-- Select (Optional) --',
    '-- নির্বাচন করুন (ডিফল্ট অ্যাকাউন্ট) --': '-- Select (Default Account) --',
    '-- নির্বাচন করুন (Select Category) --': '-- Select Category --',
    '-- কোনো ব্র্যান্ড নেই --': '-- No Brand --',
    '-- কোনো সাব-ক্যাটাগরি নেই --': '-- No Sub-category --',
    'সব লেনদেন': 'All Transactions',
    'ক্যাশ ইন': 'Cash In',
    'ক্যাশ আউট': 'Cash Out',
    'বেচা': 'Sales',
    'কেনা': 'Purchases',
    'আয়': 'Income',
    'ব্যয়': 'Expense',
    'বৃদ্ধি (ইন)': 'Increase (In)',
    'হ্রাস (আউট)': 'Decrease (Out)',
    'মজুদ (বেশি-কম)': 'Stock (High-Low)',
    'মজুদ (কম-বেশি)': 'Stock (Low-High)',
    'নিম্ন মজুদ': 'Low Stock',
    'স্টক আউট': 'Stock Out',
};

function initSelectOption(opt) {
    const $opt = $(opt);
    if ($opt.attr('data-text-en') && $opt.attr('data-text-bn')) {
        return;
    }

    let en = $opt.attr('data-en') || $opt.attr('data-text-en');
    let bn = $opt.attr('data-bn') || $opt.attr('data-text-bn');

    // Handle options with child spans like <span class="bn">...</span><span class="en">...</span>
    const $bnSpan = $opt.find('.bn');
    const $enSpan = $opt.find('.en');
    if ($bnSpan.length && $enSpan.length) {
        bn = $bnSpan.text().trim();
        en = $enSpan.text().trim();
        $opt.empty();
    }

    if (!en || !bn) {
        const rawText = $opt.text().trim();

        if (commonSelectPhrases[rawText]) {
            bn = rawText;
            en = commonSelectPhrases[rawText];
        } else {
            // Regex matches: "সকল ক্যাটাগরি (All Categories)", "সক্রিয় (Active)", "-- কোনো ব্র্যান্ড নেই (None) --"
            const match = rawText.match(/^(--\s*)?(.+?)\s*\(([^)]+)\)(\s*--)?$/);
            if (match) {
                const prefix = match[1] || '';
                const part1 = match[2].trim();
                const part2 = match[3].trim();
                const suffix = match[4] || '';

                const hasBn = /[\u0980-\u09FF]/.test(part1);
                const hasEn = /[a-zA-Z]/.test(part2);

                if (hasBn && hasEn) {
                    bn = prefix + part1 + suffix;
                    en = prefix + part2 + suffix;
                }
            }
        }
    }

    if (bn && en) {
        $opt.attr('data-text-bn', bn);
        $opt.attr('data-text-en', en);
    }
}

function initSelectOptionsLang($container) {
    const $scope = $container ? $($container) : $(document);
    $scope.find('select option').each(function () {
        initSelectOption(this);
    });
}

function updateSelectOptionsText(isEn, $container) {
    const $scope = $container ? $($container) : $(document);
    $scope.find('select option[data-text-en]').each(function () {
        const en = $(this).attr('data-text-en');
        const bn = $(this).attr('data-text-bn');
        if (en && bn) {
            const targetText = isEn ? en : bn;
            if (this.text !== targetText) {
                this.text = targetText;
            }
        }
    });
}

window.updateSelectOptionsLang = function ($container) {
    const isEn = (localStorage.getItem('lang') || 'bn') === 'en';
    initSelectOptionsLang($container);
    updateSelectOptionsText(isEn, $container);
};

function setLang(lang) {
    const isEn = lang === 'en';
    $('html').toggleClass('lang-en', isEn);
    $('body').toggleClass('lang-en', isEn);
    $('#btn-bn').toggleClass('active', !isEn);
    $('#btn-en').toggleClass('active', isEn);
    $('.switch-opt-bn').toggleClass('active', !isEn);
    $('.switch-opt-en').toggleClass('active', isEn);
    $('#lang-toggle').prop('checked', isEn);
    $('input.bn-ph').each(function () {
        const original = $(this).attr('data-bn-ph');
        $(this).attr('placeholder', isEn ? (placeholderMap[original] || original) : original);
    });
    $('[data-placeholder-en]').each(function () {
        const bn = $(this).attr('data-placeholder-bn') || $(this).attr('placeholder');
        const en = $(this).attr('data-placeholder-en');
        $(this).attr('placeholder', isEn ? en : bn);
    });
    $('.dataTables_filter input, .dt-search input').attr('placeholder', isEn ? 'Search...' : 'এখানে লিখুন...');

    initSelectOptionsLang();
    updateSelectOptionsText(isEn);

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
    initSelectOptionsLang();
    const currentLang = localStorage.getItem('lang') || 'bn';
    setLang(currentLang);

    if (window.MutationObserver) {
        const selectObserver = new MutationObserver(function (mutations) {
            let hasNewSelect = false;
            for (let i = 0; i < mutations.length; i++) {
                const added = mutations[i].addedNodes;
                for (let j = 0; j < added.length; j++) {
                    const node = added[j];
                    if (node.nodeType === 1 && (node.nodeName === 'SELECT' || node.nodeName === 'OPTION' || (node.querySelector && node.querySelector('select')))) {
                        hasNewSelect = true;
                        break;
                    }
                }
                if (hasNewSelect) break;
            }
            if (hasNewSelect) {
                const isEn = (localStorage.getItem('lang') || 'bn') === 'en';
                initSelectOptionsLang();
                updateSelectOptionsText(isEn);
            }
        });
        selectObserver.observe(document.body, { childList: true, subtree: true });
    }
}

/* ---------------- Keyboard Shortcuts & Listeners ---------------- */
$(function () {
    initSidebarState();
    updateThemeButtons();
    updateMobilePreviewToggle();
    initLang();

    // Theme Switcher Listeners
    $(document).on('change', '#theme-toggle', function () {
        setTheme($(this).is(':checked') ? 'dark' : 'light');
    });
    $(document).on('click', '[data-action="set-theme-light"], .switch-opt-light', function (e) {
        if ($(e.target).is('input')) return;
        setTheme('light');
    });
    $(document).on('click', '[data-action="set-theme-dark"], .switch-opt-dark', function (e) {
        if ($(e.target).is('input')) return;
        setTheme('dark');
    });

    // Language Switcher Listeners
    $(document).on('change', '#lang-toggle', function () {
        setLang($(this).is(':checked') ? 'en' : 'bn');
    });
    $(document).on('click', '[data-action="set-lang-bn"], .switch-opt-bn', function (e) {
        if ($(e.target).is('input')) return;
        setLang('bn');
    });
    $(document).on('click', '[data-action="set-lang-en"], .switch-opt-en', function (e) {
        if ($(e.target).is('input')) return;
        setLang('en');
    });

    // Topbar Shop Switcher Dropdown
    $(document).on('click', '.btn-shop-switcher', function (e) {
        e.stopPropagation();
        $(this).siblings('.shop-switcher-menu').fadeToggle(120);
    });
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.shop-switcher-dropdown').length) {
            $('.shop-switcher-menu').hide();
        }
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
                if ($form[0]) {
                    $form[0].submit();
                } else {
                    $form.trigger('submit');
                }
            }
        });
    });
}

/* ---------------- Accordion & Feature Box Behaviors ---------------- */
function toggleAccordion($box, forceOpen) {
    if (!$box || !$box.length) return;
    const $content = $box.find('[data-accordion-content], .app-accordion-body, .app-accordion-content');
    const isOpen = typeof forceOpen === 'boolean' ? forceOpen : !$box.hasClass('is-open');

    // Handle group exclusivity
    const groupName = $box.data('accordion-group');
    if (isOpen && groupName) {
        $(`[data-accordion][data-accordion-group="${groupName}"]`).not($box).each(function () {
            const $otherBox = $(this);
            const $otherContent = $otherBox.find('[data-accordion-content], .app-accordion-body, .app-accordion-content');
            const $otherCheckbox = $otherBox.find('[data-accordion-checkbox], input[type="checkbox"]');
            $otherBox.removeClass('is-open active');
            $otherContent.stop(true, true).slideUp(180);
            if ($otherCheckbox.length) {
                $otherCheckbox.prop('checked', false);
            }
        });
    }

    if (isOpen) {
        $box.addClass('is-open active');
        $content.stop(true, true).slideDown(180);
    } else {
        $box.removeClass('is-open active');
        $content.stop(true, true).slideUp(180);
    }
}

function initAccordionBehaviors() {
    // 1. Trigger clicked (Header)
    $(document).on('click', '[data-accordion-trigger], .feature-box-toggle', function (e) {
        // Prevent firing on interactive elements inside the header (like inputs, buttons, selects, links)
        if ($(e.target).closest('input, select, textarea, button:not([data-accordion-trigger]), a').length) {
            return;
        }

        const $box = $(this).closest('[data-accordion], .feature-box');
        const $label = $(e.target).closest('label');
        const $checkbox = $box.find('[data-accordion-checkbox], input[type="checkbox"]');

        // If user clicked inside a label associated with a checkbox, native browser event toggles checkbox and triggers 'change'
        if ($label.length && $checkbox.length) {
            return;
        }

        if ($checkbox.length && !$checkbox.prop('disabled')) {
            // Clicked outside label (empty space or chevron icon)
            $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
        } else if (!$checkbox.length) {
            // Standard accordion without checkbox
            toggleAccordion($box);
        }
    });

    // 2. Checkbox change event
    $(document).on('change', '[data-accordion-checkbox], [data-accordion] input[type="checkbox"], .feature-box input[type="checkbox"]', function () {
        const $box = $(this).closest('[data-accordion], .feature-box');
        const isChecked = $(this).is(':checked');
        toggleAccordion($box, isChecked);
    });
}

/* ---------------- Status Segmented Switcher Behaviors ---------------- */
function initStatusSwitcherBehaviors() {
    // 1. Click on Left/Right option
    $(document).on('click', '[data-status-switcher] [data-status-opt]', function (e) {
        const $opt = $(this);
        const $switcher = $opt.closest('[data-status-switcher]');
        const $wrapper = $switcher.closest('.status-toggle-wrapper');
        const $toggle = $switcher.find('[data-status-toggle]');
        const selectedVal = String($opt.data('status-opt'));
        const activeVal = String($toggle.data('active-val') || 'active');

        if ($toggle.prop('disabled')) return;

        const isNowActive = selectedVal === activeVal;
        $toggle.prop('checked', !isNowActive);

        updateStatusSwitcherState($switcher, $wrapper, isNowActive, selectedVal);
    });

    // 2. Click / Change on Center Checkbox Slider
    $(document).on('change', '[data-status-toggle]', function () {
        const $toggle = $(this);
        const $switcher = $toggle.closest('[data-status-switcher]');
        const $wrapper = $switcher.closest('.status-toggle-wrapper');
        const isChecked = $toggle.is(':checked'); // checked means inactive (right)
        const activeVal = String($toggle.data('active-val') || 'active');
        const inactiveVal = String($toggle.data('inactive-val') || 'inactive');

        const isNowActive = !isChecked;
        const selectedVal = isNowActive ? activeVal : inactiveVal;

        updateStatusSwitcherState($switcher, $wrapper, isNowActive, selectedVal);
    });

    function updateStatusSwitcherState($switcher, $wrapper, isActive, selectedVal) {
        $switcher.toggleClass('is-active', isActive).toggleClass('is-inactive', !isActive);
        $switcher.find('.switch-opt-active').toggleClass('active', isActive);
        $switcher.find('.switch-opt-inactive').toggleClass('active', !isActive);

        const $hidden = $wrapper.find('[data-status-input], input[type="hidden"]');
        if ($hidden.length) {
            $hidden.val(selectedVal).trigger('change');
        }
    }
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
window.toggleAccordion = toggleAccordion;
window.initAccordionBehaviors = initAccordionBehaviors;
window.initStatusSwitcherBehaviors = initStatusSwitcherBehaviors;
window.toEnglishDigits = toEnglishDigits;
window.isNumberInput = isNumberInput;
$.toEnglishDigits = toEnglishDigits;

$(function () {
    initTableBehaviors();
    initButtonGroupBehaviors();
    initDeleteConfirmBehaviors();
    initAccordionBehaviors();
    initStatusSwitcherBehaviors();
    initNumberStepperBehaviors();
    initEnglishNumberInputBehaviors();

    // Initialize Lucide Icons globally
    createIcons({ icons });

    // Re-initialize Lucide Icons on dynamic DOM modifications
    $(document).on('draw.dt ajaxComplete shown.bs.modal', function () {
        createIcons({ icons });
    });
});

/* ---------------- English Number Input Conversion & Enforcement ---------------- */
const BN_TO_EN_DIGIT_MAP = {
    '০': '0',
    '১': '1',
    '২': '2',
    '৩': '3',
    '৪': '4',
    '৫': '5',
    '৬': '6',
    '৭': '7',
    '৮': '8',
    '৯': '9',
    '।': '.', // Bengali dari/danda to decimal point
    '٫': '.', // Arabic decimal separator
    '٬': ',', // Arabic thousands separator
};

function toEnglishDigits(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[০-৯।٫٬]/g, function (ch) {
        return BN_TO_EN_DIGIT_MAP[ch] !== undefined ? BN_TO_EN_DIGIT_MAP[ch] : ch;
    });
}

function isNumberInput(el) {
    if (!el || el.tagName !== 'INPUT') return false;

    const type = (el.getAttribute('type') || el.type || 'text').toLowerCase();
    if (type === 'number' || type === 'tel') return true;

    const inputMode = (el.getAttribute('inputmode') || el.inputMode || '').toLowerCase();
    if (inputMode === 'numeric' || inputMode === 'decimal') return true;

    if (el.dataset && (el.dataset.type === 'number' || el.dataset.numeric === 'true')) {
        return true;
    }

    const cls = el.className || '';
    if (/\b(is-number|numeric|form-control-number|ci-qty|ci-price|ci-discount-raw|ci-warranty-custom-n|ci-received-qty|receive-qty-input|qty-input|payment-amount|table-amount)\b/i.test(cls)) {
        return true;
    }

    const name = (el.name || '').toLowerCase();
    const id = (el.id || '').toLowerCase();
    const numericPattern = /(phone|mobile|price|qty|quantity|amount|discount|tax|vat|rate|cost|charge|due|balance|total|subtotal|alert|factor|duration|opening_due|wholesale)/i;
    if (numericPattern.test(name) || numericPattern.test(id)) {
        return true;
    }

    return false;
}

function applyEnglishNumberAttrs(el) {
    if (!el || el.nodeType !== 1) return;
    if (!el.hasAttribute('lang') || el.getAttribute('lang') !== 'en') {
        el.setAttribute('lang', 'en');
    }
    if (!el.hasAttribute('dir') || el.getAttribute('dir') !== 'ltr') {
        el.setAttribute('dir', 'ltr');
    }
}

function insertTextIntoField(input, text) {
    if (!input) return;

    let inserted = false;
    try {
        inserted = document.execCommand && document.execCommand('insertText', false, text);
    } catch (e) {
        inserted = false;
    }

    if (!inserted) {
        try {
            const isNumberType = input.type === 'number';
            if (isNumberType) {
                input.type = 'text';
            }
            const start = typeof input.selectionStart === 'number' ? input.selectionStart : input.value.length;
            const end = typeof input.selectionEnd === 'number' ? input.selectionEnd : input.value.length;
            const val = input.value || '';
            input.value = val.slice(0, start) + text + val.slice(end);
            const nextPos = start + text.length;
            input.setSelectionRange(nextPos, nextPos);
            if (isNumberType) {
                input.type = 'number';
            }
        } catch (err) {
            input.value = toEnglishDigits(input.value) + text;
        }
    }

    $(input).trigger('input').trigger('change');
}

function initEnglishNumberInputBehaviors() {
    // 1. Tag existing number inputs on page load
    $('input').each(function () {
        if (isNumberInput(this)) {
            applyEnglishNumberAttrs(this);
            if (this.value && /[০-৯।٫٬]/.test(this.value)) {
                this.value = toEnglishDigits(this.value);
            }
        }
    });

    // 2. Intercept physical typing on Bengali keyboard / IME (Avro, Bijoy, etc.)
    $(document).on('keydown', function (e) {
        if (e.ctrlKey || e.altKey || e.metaKey) return;
        if (!isNumberInput(e.target)) return;

        const enDigit = BN_TO_EN_DIGIT_MAP[e.key];
        if (enDigit !== undefined) {
            e.preventDefault();
            insertTextIntoField(e.target, enDigit);
        }
    });

    // 3. Intercept input composition / virtual keyboard insertions (Mobile keyboards, Android Gboard, etc.)
    $(document).on('beforeinput', function (e) {
        if (!isNumberInput(e.target)) return;
        const orig = e.originalEvent || e;
        const data = orig.data;
        if (data && /[০-৯।٫٬]/.test(data)) {
            orig.preventDefault();
            insertTextIntoField(e.target, toEnglishDigits(data));
        }
    });

    // 4. Intercept paste events containing Bengali digits
    $(document).on('paste', function (e) {
        if (!isNumberInput(e.target)) return;
        const clip = (e.originalEvent || e).clipboardData || window.clipboardData;
        if (!clip) return;
        const pasted = clip.getData('text');
        if (pasted && /[০-৯।٫٬]/.test(pasted)) {
            e.preventDefault();
            insertTextIntoField(e.target, toEnglishDigits(pasted));
        }
    });

    // 5. Real-time input sanitizer safeguard (autofill, drag-and-drop, voice input)
    $(document).on('input', function (e) {
        if (!isNumberInput(e.target)) return;
        if (e.target.value && /[০-৯।٫٬]/.test(e.target.value)) {
            const clean = toEnglishDigits(e.target.value);
            if (clean !== e.target.value) {
                const canSelect = e.target.type !== 'number' && typeof e.target.selectionStart === 'number';
                const start = canSelect ? e.target.selectionStart : null;
                e.target.value = clean;
                if (start !== null) {
                    try { e.target.setSelectionRange(start, start); } catch (err) {}
                }
                $(e.target).trigger('change');
            }
        }
    });

    // 6. Blur / Change enforcement
    $(document).on('blur change', function (e) {
        if (!isNumberInput(e.target)) return;
        if (e.target.value && /[০-৯।٫٬]/.test(e.target.value)) {
            e.target.value = toEnglishDigits(e.target.value);
            $(e.target).trigger('input');
        }
    });

    // 7. Focus hook to ensure lang="en" and dir="ltr" are set for keyboard hint
    $(document).on('focus', function (e) {
        if (isNumberInput(e.target)) {
            applyEnglishNumberAttrs(e.target);
        }
    });

    // 8. Observe dynamic DOM changes (modals, Ajax DataTables, dynamic rows)
    if (window.MutationObserver) {
        const numberInputObserver = new MutationObserver(function (mutations) {
            for (let i = 0; i < mutations.length; i++) {
                const added = mutations[i].addedNodes;
                for (let j = 0; j < added.length; j++) {
                    const node = added[j];
                    if (node.nodeType === 1) {
                        if (node.tagName === 'INPUT' && isNumberInput(node)) {
                            applyEnglishNumberAttrs(node);
                            if (node.value && /[০-৯।٫٬]/.test(node.value)) {
                                node.value = toEnglishDigits(node.value);
                            }
                        } else if (node.querySelectorAll) {
                            const inputs = node.querySelectorAll('input');
                            for (let k = 0; k < inputs.length; k++) {
                                if (isNumberInput(inputs[k])) {
                                    applyEnglishNumberAttrs(inputs[k]);
                                    if (inputs[k].value && /[০-৯।٫٬]/.test(inputs[k].value)) {
                                        inputs[k].value = toEnglishDigits(inputs[k].value);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        });
        numberInputObserver.observe(document.body, { childList: true, subtree: true });
    }
}

/* ---------------- Modern Number Input Stepper ---------------- */
function initNumberStepperBehaviors() {
    $(document).on('click', '.form-stepper-btn', function (e) {
        e.preventDefault();
        const isUp = $(this).hasClass('form-stepper-up');
        const $input = $(this).closest('.form-input-group').find('input[type="number"]');
        if (!$input.length) return;
        const inp = $input[0];
        if (inp.disabled || inp.readOnly) return;

        if (inp.value && /[০-৯।٫٬]/.test(inp.value)) {
            inp.value = toEnglishDigits(inp.value);
        }

        try {
            if (isUp) {
                inp.stepUp();
            } else {
                inp.stepDown();
            }
        } catch (err) {
            const step = parseFloat(inp.step) || 1;
            const val = parseFloat(inp.value) || 0;
            const min = inp.min !== '' ? parseFloat(inp.min) : -Infinity;
            const max = inp.max !== '' ? parseFloat(inp.max) : Infinity;
            let next = isUp ? val + step : val - step;
            if (next < min) next = min;
            if (next > max) next = max;
            const decimals = (step.toString().split('.')[1] || '').length;
            inp.value = decimals > 0 ? next.toFixed(decimals) : next;
        }
        $input.trigger('input').trigger('change');
    });
}





