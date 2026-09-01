# Project Guidelines & Rules (sng-pos-v2)

## Frontend Development Standards
- **Always use jQuery (`$`)** instead of vanilla JavaScript (`document.querySelector`, `addEventListener`, etc.) for all frontend scripting, DOM manipulations, event handlers, animations, and AJAX requests.
- Ensure jQuery is imported and exposed globally on `window.$` and `window.jQuery`.
- Use idiomatic jQuery conventions:
  - `$(function () { ... });` for document ready lifecycle.
  - `$(document).on('click', '.selector', function () { ... });` for event listeners and dynamic elements.
  - `$.ajax({ ... })`, `$.get(...)`, `$.post(...)` for asynchronous calls with Laravel CSRF header.
