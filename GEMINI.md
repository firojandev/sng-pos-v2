# Project Guidelines & Rules (sng-pos-v2)

## Frontend Development Standards
- **Always use jQuery (`$`)** instead of vanilla JavaScript (`document.querySelector`, `addEventListener`, etc.) for all frontend scripting, DOM manipulations, event handlers, animations, and AJAX requests.
- Ensure jQuery is imported and exposed globally on `window.$` and `window.jQuery`.
- Use idiomatic jQuery conventions:
  - `$(function () { ... });` for document ready lifecycle.
  - `$(document).on('click', '.selector', function () { ... });` for event listeners and dynamic elements.
  - `$.ajax({ ... })`, `$.get(...)`, `$.post(...)` for asynchronous calls with Laravel CSRF header.

## UI Component Standards
- **Always use reusable Core Blade UI components** from `Modules/Core/resources/views/components/` instead of writing raw unstyled HTML elements:
  - `<x-core::button>` for all buttons, links, and modal triggers (supports `variant`, `color`, `size`, `icon`, `loading`, `badge`).
  - `<x-core::input>` for text, number, date, search inputs (supports `icon`, `size`, `placeholder`, `label`, `error`, `clearable`).
  - `<x-core::select>` for select dropdowns (supports `options`, `size`, `label`, `error`, `icon`).
  - `<x-core::badge>` for status, category, and type badges (supports `color`, `size`, `variant`, `rounded`, `dot`, `icon`).
  - `<x-core::toggle>` / `<x-core::checkbox>` / `<x-core::radio>` for form controls.
  - `<x-core::icon>` for scalable Lucide & SVG icons.

### Button Standards
- **Primary Button**: **Always use `<x-core::button color="primary">` (or default `<x-core::button>`) — do NOT use `gold`**. Use for primary page actions, creation triggers, and form submissions.
- **Secondary Button**: Use `<x-core::button variant="secondary">` (or `<x-core::button color="secondary">`) for secondary actions, filters, and cancel buttons.
- **Danger / Delete Button**: Use `<x-core::button color="danger">` (or `<x-core::button color="red">` / `<x-core::button variant="soft" color="danger">`) for delete, destructive, and cancellation operations.
- **Button Size**: **Always use `size="sm"`** (`<x-core::button size="sm" ...>`) across page header actions, filters, table row actions, modals, and form submission buttons unless explicitly requested otherwise.

### Form Element Standards
- **Strict Component Usage**: **NEVER write raw HTML `<input>`, `<select>`, `<textarea>`, or `<button>` tags** in views, filter rows, modals, or forms.
- **Form Element Size**: **Always use `size="sm"`** across all `<x-core::input size="sm">`, `<x-core::select size="sm">`, `<x-core::textarea size="sm">`, and `<x-core::button size="sm">` across filters, search bars, modals, tables, and forms.
- **Filters & Inline Bars**: When using `<x-core::input>` or `<x-core::select>` inside `.filters` or inline toolbars without labels:
  - Always specify `size="sm"` and `:no-margin="true"` to prevent unwanted spacing.
  - Wrap each component in a container with a designated width (`style="width: ...; flex-shrink: 0;"`, or `flex: 1; min-width: 200px;` for search) because `.form-input-group` has a default `width: 100%`.
  - Ensure the toolbar container specifies `display: flex; align-items: center; flex-wrap: nowrap; gap: 8px;` so all filters, date pickers, and buttons remain strictly on one single line, perfectly aligned.

### Icon Standards
- **Edit Action**: **Always use icon `edit`** (`icon="edit"` or `<x-core::icon name="edit" />`).
- **Delete Action**: **Always use icon `trash-2`** (`icon="trash-2"` or `<x-core::icon name="trash-2" />`).

## Table Empty State Standards
- **Always use `<x-core::table.empty>`** inside the `@empty` block of all Blade tables and list cards:
  - Example: `<tr><td colspan="..."><x-core::table.empty icon="package" title="কোনো পণ্য নেই" title-en="No products found" /></td></tr>`
  - Never use raw unstyled text or simple `helper` divs for empty table states.
  - In `BaseDataTable`, styled empty state markup with icon and bilingual text is used for `emptyTable` and `zeroRecords`.

## Confirmation Dialog & SweetAlert Standards
- **Always use SweetAlert2 (`Swal` / `confirmDelete()`)** instead of native browser `confirm()` or `alert()`.
- For destructive/delete form submissions, add `class="delete-form"` (or `data-confirm-delete`) to the `<form>` element with optional `data-title="..."` and `data-text="..."`. The global handler automatically intercepts and triggers a styled SweetAlert2 confirmation modal with dark-mode and bilingual support.
- For custom programmatic confirmation prompts, use `Swal.fire({ ... })` or `window.confirmDelete(options)`.

## Typography & Font Standards
- **Always use `Noto Sans Bengali` font across the entire website**:
  - The primary Bengali font family must always be `'Noto Sans Bengali'` (with `'SolaimanLipi'`, `'Hind Siliguri'`, `'Plus Jakarta Sans'`, `'Inter'`, `sans-serif` as fallback).
  - Headings (`h1`-`h6`), page titles, topbar titles, and modal/drawer headers must use `'Noto Sans Bengali', 'SolaimanLipi', 'Baloo Da 2', 'Plus Jakarta Sans', sans-serif`.
  - All form controls (`input`, `select`, `textarea`, `button`) must inherit or specify `'Noto Sans Bengali'`.
  - Never override font-family with standalone `Hind Siliguri` or `Baloo Da 2` without placing `'Noto Sans Bengali'` first.
  - When creating new layouts or document views, always include the Google Fonts link for Noto Sans Bengali (`https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap`) or local `@font-face` from `public/fonts/`.

## Dark Mode & Color Design Token Standards
- **Never use hardcoded hex colors for backgrounds, texts, or borders** (e.g. `#fff`, `#ffffff`, `#f8fafc`, `#f1f5f9`, `#e2e8f0`, `#cbd5e1`, `#0f172a`, `#1e293b`, `#334155`, `#475569`, `#64748b`, `#94a3b8`) directly in inline styles or scoped CSS.
- **Always use CSS custom properties (variables)** defined in `resources/css/app.css` to guarantee complete, automated synchronization between light and dark themes:
  - **Cards, Panels & Popups**: `background: var(--card);` (white in light mode, `#111827` in dark mode).
  - **Page Background / Canvas / Sub-cards**: `background: var(--paper);` (`#f8fafc` in light, `#0b0f19` in dark).
  - **Subtle Row Backgrounds & Progress Tracks**: `background: var(--paper-line);` (`#f1f5f9` in light, `#1e293b` in dark).
  - **Borders & Dividers**: `border: 1px solid var(--border);` (`#e2e8f0` in light, `#1f293d` in dark).
  - **Headings & Primary Text**: `color: var(--ink-900);` (`#0f172a` in light, `#f8fafc` in dark).
  - **Sub-headings, Labels & Important Subtext**: `color: var(--ink-700);` (`#334155` in light, `#cbd5e1` in dark).
  - **Body / Secondary Text**: `color: var(--ink-600);` (`#475569` in light, `#94a3b8` in dark).
  - **Muted Hints, Footnotes & Secondary Icons**: `color: var(--ink-400);` (`#94a3b8` in light, `#64748b` in dark).
  - **Box Shadows**: `box-shadow: var(--shadow-card);` or `var(--shadow-sm);`.
  - **Status & Metric Pills**:
    - Blue / Info / Pending: `background: var(--blue-100); border-color: var(--blue-ic-bg); color: var(--blue-ink);`
    - Green / Success / Received: `background: var(--green-100); border-color: var(--green-ic-bg); color: var(--green-ink);`
    - Amber / Warning: `background: var(--gold-100); color: var(--gold-ink);`
    - Red / Danger: `background: var(--red-100); color: var(--red-600);` or `var(--red-ink);`
- **Dynamic Elements, Search Menus & Dropdowns**:
  - Live search results, dropdown menus, autocompletes must specify `background: var(--card); border: 1px solid var(--border); color: var(--ink-900); box-shadow: var(--shadow-card);`.
  - Item hover states must specify `background: var(--paper);` so interactive states look native in dark mode.


