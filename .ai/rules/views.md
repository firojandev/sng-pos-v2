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
