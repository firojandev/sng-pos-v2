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

