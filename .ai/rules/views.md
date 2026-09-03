---
paths:
  - 'resources/views/**,Modules/**/resources/views/**'
---

# Views

## Use Core Reusable Blade UI Components and jQuery
Always use the reusable Core Blade components located in `Modules/Core/resources/views/components/` (such as `<x-core::button>`, `<x-core::input>`, `<x-core::select>`, `<x-core::badge>`, `<x-core::form-group>`, `<x-core::toggle>`, `<x-core::icon>`) instead of writing raw unstyled/custom HTML inputs, selects, and buttons. Ensure all frontend interactive logic adheres to jQuery (`$`) standard.

## Primary, Secondary, and Danger Button Component Standards
Button Standards:
- Primary Button: Use `<x-core::button color="gold">` (or `<x-core::button color="primary">` or default `<x-core::button>`) for primary actions, create triggers, and submit buttons.
- Secondary Button: Use `<x-core::button variant="secondary">` (or `<x-core::button color="secondary">`) for secondary actions, filters, and cancel buttons.
- Danger / Delete Button: Use `<x-core::button color="danger">` (or `<x-core::button color="red">` / `<x-core::button variant="soft" color="danger">`) for delete, destructive, and cancellation operations.

## Enforce Primary Button Standards (No Gold)
Button Standards:
- Primary Button: ALWAYS use `<x-core::button color="primary">` (or default `<x-core::button>`) — never use `gold`. Use for primary page actions, create/save triggers, and form submissions.
- Secondary Button: Use `<x-core::button variant="secondary">` (or `<x-core::button color="secondary">`) for secondary actions, filters, and cancel buttons.
- Danger / Delete Button: Use `<x-core::button color="danger">` (or `<x-core::button color="red">` / `<x-core::button variant="soft" color="danger">`) for delete, destructive, and cancellation operations.

## Action Icon Standards (edit and trash-2)
Icon Standards:
- Edit Action: ALWAYS use icon `edit` (`icon="edit"` or `<x-core::icon name="edit" />`).
- Delete / Destroy Action: ALWAYS use icon `trash-2` (`icon="trash-2"` or `<x-core::icon name="trash-2" />`).

## Always Use SweetAlert for Confirmation Dialogs
Confirmation Dialog Standards:
- ALWAYS use SweetAlert2 (`Swal` / `window.confirmDelete()`) instead of raw native browser `confirm()` or `alert()`.
- For delete/destroy forms, add `class="delete-form"` (or `data-confirm-delete`) to the `<form>` with optional `data-title="..."` and `data-text="..."`. The global handler in `app.js` automatically manages the styled modal and submission.
- For custom JavaScript confirmation dialogs, call `Swal.fire({ ... })` or `window.confirmDelete(options)`.

## Always Use Core Table Empty State Component (<x-core::table.empty>)
ALWAYS use the `<x-core::table.empty>` component (located in `Modules/Core/resources/views/components/table/empty.blade.php`) inside the `@empty` block of all tables and data grids. Provide appropriate `icon`, `title`, `title-en`, and optional `description` / `description-en` props. Never use raw unstyled text or simple `helper` divs for empty table states. In BaseDataTable, styled empty state markup with icon and bilingual text is used for `emptyTable` and `zeroRecords`.

## Typography & Font Standards (Always Use Noto Sans Bengali)
ALWAYS use `'Noto Sans Bengali'` as the primary Bengali font family across the whole website (body, headings `h1`-`h6`, buttons, inputs, selects, tables, and dialogs). Ensure the font stack is: `'Noto Sans Bengali', 'SolaimanLipi', 'Hind Siliguri', 'Plus Jakarta Sans', 'Inter', sans-serif`. When creating or styling custom elements or views, never override font-family with standalone Bengali fonts alone; always place `'Noto Sans Bengali'` first. Include the Google Fonts link (`https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap`) or local `@font-face` from `public/fonts/`.

## Strict Component Usage & Size Standard: Always Use <x-core::*> in SM Size (size="sm")
- **NEVER use raw HTML `<input>`, `<select>`, `<textarea>`, or `<button>` tags** in views, filter rows, modals, or forms.
- **ALWAYS use Core Blade UI components**:
  - `<x-core::input size="sm" ...>` for all text, search, date, number, and password inputs. For filters or compact bars without labels, pass `:no-margin="true"`.
  - `<x-core::select size="sm" ...>` for all select dropdowns. For filters or compact bars without labels, pass `:no-margin="true"`.
  - `<x-core::textarea size="sm" ...>` for all multiline text inputs.
  - `<x-core::button size="sm" ...>` for all buttons, triggers, submit actions, and links.
- **Strict Size Enforcement**: Every `<x-core::input>`, `<x-core::select>`, `<x-core::textarea>`, and `<x-core::button>` MUST specify `size="sm"` across all page header actions, filters, table row actions, modals, and form submission buttons unless explicitly requested otherwise.
- **Filters & Inline Bars Single-Line Alignment**: When using `<x-core::input>` or `<x-core::select>` inside `.filters` or inline toolbars:
  - Always specify `size="sm"` and `:no-margin="true"`.
  - Wrap each component in a container with a designated width (`style="width: ...; flex-shrink: 0;"`, or `flex: 1; min-width: 200px;` for search) because `.form-input-group` has a default `width: 100%`.
  - Ensure the toolbar container specifies `display: flex; align-items: center; flex-wrap: nowrap; gap: 8px;` so all filters, date pickers, and buttons remain strictly on one single line, perfectly aligned.

## Dark Mode and Color Design Tokens Standard
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
