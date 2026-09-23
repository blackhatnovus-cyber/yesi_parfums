document.documentElement.classList.add('js');

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const sidebar = document.querySelector('#adminSidebar');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebarOverlay = document.querySelector('[data-sidebar-overlay]');
const mobileSidebarQuery = window.matchMedia('(max-width: 1040px)');
let searchTimer;
let searchController;
let sidebarReturnFocus;

function showAdminToast(message, type = 'success') {
    const region = document.querySelector('#adminToastRegion');
    if (! region || ! message) return;

    const toast = document.createElement('div');
    toast.className = `admin-toast${type === 'error' ? ' is-error' : ''}`;
    toast.textContent = message;
    region.append(toast);
    window.setTimeout(() => toast.remove(), 4200);
}

function openSidebar() {
    if (! sidebar || ! sidebarToggle || ! sidebarOverlay) return;
    sidebarReturnFocus = document.activeElement;
    sidebar.classList.add('is-open');
    sidebar.setAttribute('aria-hidden', 'false');
    sidebarToggle.setAttribute('aria-expanded', 'true');
    sidebarOverlay.hidden = false;
    document.body.classList.add('is-sidebar-open');
    sidebar.querySelector('a')?.focus();
}

function closeSidebar({ restoreFocus = true } = {}) {
    if (! sidebar || ! sidebarToggle || ! sidebarOverlay) return;
    sidebar.classList.remove('is-open');
    sidebarToggle.setAttribute('aria-expanded', 'false');
    sidebarOverlay.hidden = true;
    document.body.classList.remove('is-sidebar-open');
    if (mobileSidebarQuery.matches) sidebar.setAttribute('aria-hidden', 'true');
    if (restoreFocus && sidebarReturnFocus instanceof HTMLElement) sidebarReturnFocus.focus();
    sidebarReturnFocus = null;
}

function syncSidebarMode() {
    if (! sidebar || ! sidebarOverlay) return;
    if (mobileSidebarQuery.matches) {
        if (! sidebar.classList.contains('is-open')) sidebar.setAttribute('aria-hidden', 'true');
    } else {
        sidebar.classList.remove('is-open');
        sidebar.setAttribute('aria-hidden', 'false');
        sidebarOverlay.hidden = true;
        document.body.classList.remove('is-sidebar-open');
    }
}

async function parseJsonResponse(response) {
    const payload = await response.json().catch(() => ({ message: 'The management request could not be completed.' }));

    if (! response.ok) {
        const firstError = payload.errors ? Object.values(payload.errors).flat()[0] : null;
        throw new Error(firstError ?? payload.message ?? 'The management request could not be completed.');
    }

    return payload;
}

async function refreshTable(form, url = null) {
    const region = document.querySelector('[data-admin-table-region]');
    if (! region) return;

    searchController?.abort();
    searchController = new AbortController();
    const endpoint = url ?? form.dataset.searchEndpoint ?? form.action;
    const requestUrl = url ?? `${endpoint}?${new URLSearchParams(new FormData(form)).toString()}`;
    region.setAttribute('aria-busy', 'true');
    region.classList.add('is-loading');

    try {
        const response = await fetch(requestUrl, {
            method: 'GET',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            signal: searchController.signal,
        });
        const payload = await parseJsonResponse(response);
        region.innerHTML = payload.html;
        const resultCount = document.querySelector('[data-result-count]');
        if (resultCount) resultCount.textContent = String(payload.count);
        window.history.replaceState({}, '', requestUrl);
    } catch (error) {
        if (error.name !== 'AbortError') showAdminToast(error.message, 'error');
    } finally {
        region.setAttribute('aria-busy', 'false');
        region.classList.remove('is-loading');
    }
}

async function submitStatusForm(form) {
    const formData = new FormData(form);
    const controls = form.querySelectorAll('button, select');
    controls.forEach((control) => { control.disabled = true; });

    try {
        const response = await fetch(form.action, {
            method: form.method.toUpperCase(),
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });
        const payload = await parseJsonResponse(response);
        const badge = form.closest('tr, .order-detail-grid, .message-detail')?.querySelector('.status-badge');
        if (badge && payload.status) {
            badge.className = `status-badge is-${payload.status}`;
            badge.textContent = payload.status.charAt(0).toUpperCase() + payload.status.slice(1);
        }
        showAdminToast(payload.message);
    } catch (error) {
        showAdminToast(error.message, 'error');
    } finally {
        controls.forEach((control) => { control.disabled = false; });
    }
}

sidebarToggle?.addEventListener('click', () => {
    if (sidebar?.classList.contains('is-open')) closeSidebar();
    else openSidebar();
});
sidebarOverlay?.addEventListener('click', () => closeSidebar());
mobileSidebarQuery.addEventListener('change', syncSidebarMode);
syncSidebarMode();

document.querySelectorAll('[data-admin-search-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        refreshTable(form);
    });
    form.querySelector('[data-debounced-search]')?.addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => refreshTable(form), 320);
    });
    form.querySelectorAll('[data-immediate-filter]').forEach((select) => {
        select.addEventListener('change', () => refreshTable(form));
    });
});

document.addEventListener('submit', (event) => {
    const confirmationForm = event.target.closest('[data-confirm]');
    if (confirmationForm && ! window.confirm(confirmationForm.dataset.confirm)) {
        event.preventDefault();
        event.stopImmediatePropagation();
        return;
    }

    const statusForm = event.target.closest('[data-status-form], [data-async-status]');
    if (statusForm) {
        event.preventDefault();
        submitStatusForm(statusForm);
        return;
    }

    const adminForm = event.target.closest('[data-admin-form]');
    const submitButton = adminForm?.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = 'Saving…';
    }
});

document.addEventListener('change', (event) => {
    const statusSelect = event.target.closest('[data-status-select]');
    if (statusSelect) submitStatusForm(statusSelect.form);

    const imageInput = event.target.closest('[data-image-input]');
    if (imageInput?.files?.[0]) {
        const previewImage = imageInput.closest('form')?.querySelector('[data-image-preview] img');
        if (previewImage) {
            const objectUrl = URL.createObjectURL(imageInput.files[0]);
            previewImage.addEventListener('load', () => URL.revokeObjectURL(objectUrl), { once: true });
            previewImage.src = objectUrl;
        }
    }
});

document.addEventListener('click', (event) => {
    if (mobileSidebarQuery.matches && event.target.closest('.admin-nav a, .admin-sidebar-footer a')) {
        closeSidebar({ restoreFocus: false });
    }

    const paginationLink = event.target.closest('[data-admin-table-region] .admin-pagination a');
    const searchForm = document.querySelector('[data-admin-search-form]');
    if (paginationLink && searchForm) {
        event.preventDefault();
        refreshTable(searchForm, paginationLink.href);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && sidebar?.classList.contains('is-open')) closeSidebar();
});

const flash = document.querySelector('#adminFlash');
if (flash?.dataset.message) showAdminToast(flash.dataset.message, flash.dataset.type);
