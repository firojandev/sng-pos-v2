/* ---------------- Toast ---------------- */
let toastTimer = null;
function toast(bn, en) {
    const el = document.getElementById('toast');
    if (!el) return;
    el.textContent = document.body.classList.contains('lang-en') ? en : bn;
    el.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('show'), 2200);
}

/* ---------------- Sidebar (mobile) ---------------- */
function toggleSidebar(open) {
    document.getElementById('sidebar')?.classList.toggle('open', open);
    document.getElementById('overlay')?.classList.toggle('show', open);
}

/* ---------------- Generic modal ---------------- */
function openModal(id) {
    document.getElementById(id)?.classList.add('open');
}
function closeModal(id) {
    document.getElementById(id)?.classList.remove('open');
}

/* ---------------- Print a section ---------------- */
function printSection(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.add('print-only');
    window.print();
    el.classList.remove('print-only');
}

/* ---------------- Language switch ---------------- */
const placeholderMap = {
    'খুঁজুন...': 'Search...',
};

function setLang(lang) {
    document.body.classList.toggle('lang-en', lang === 'en');
    document.getElementById('btn-bn')?.classList.toggle('active', lang === 'bn');
    document.getElementById('btn-en')?.classList.toggle('active', lang === 'en');
    document.querySelectorAll('input.bn-ph').forEach((el) => {
        const original = el.getAttribute('data-bn-ph');
        el.placeholder = lang === 'en' ? (placeholderMap[original] || original) : original;
    });
    localStorage.setItem('lang', lang);
}

function initLang() {
    document.querySelectorAll('input.bn-ph').forEach((el) => {
        el.setAttribute('data-bn-ph', el.placeholder);
    });
    setLang(localStorage.getItem('lang') || 'bn');
}

document.addEventListener('DOMContentLoaded', initLang);

window.toast = toast;
window.toggleSidebar = toggleSidebar;
window.setLang = setLang;
window.openModal = openModal;
window.closeModal = closeModal;
window.printSection = printSection;
