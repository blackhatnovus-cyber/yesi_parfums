const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const searchEndpoint = document.querySelector('meta[name="search-endpoint"]')?.content ?? '';
const menuToggle = document.querySelector('#menuToggle');
const menuClose = document.querySelector('#menuClose');
const mobileNav = document.querySelector('#mobileNav');
const navOverlay = document.querySelector('#navOverlay');
const searchInput = document.querySelector('#globalSearch');
const searchForm = document.querySelector('#headerSearchForm');
const searchPanel = document.querySelector('#searchPanel');
const mobileSearch = document.querySelector('#mobileSearch');
const mobileSearchForm = document.querySelector('#mobileSearchForm');
const mobileSearchPanel = document.querySelector('#mobileSearchPanel');
let searchTimer;
let previousFocus;
let shopCarouselController;

function escapeHtml(value = '') {
    return String(value).replace(/[&<>'"]/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;',
    })[character]);
}

function showToast(message, type = 'success') {
    const region = document.querySelector('#toastRegion');
    if (! region) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type === 'error' ? 'error' : ''}`;
    toast.textContent = message;
    region.append(toast);
    window.setTimeout(() => toast.remove(), 3800);
}

function updateCounts(selector, value) {
    document.querySelectorAll(selector).forEach((element) => {
        element.textContent = String(value);
    });
}

async function requestJson(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers ?? {}),
        },
    });
    const payload = await response.json().catch(() => ({ message: 'The atelier could not process that request.' }));

    if (response.status === 401 && payload.login_url) {
        showToast(payload.message, 'error');
        window.setTimeout(() => window.location.assign(payload.login_url), 650);
        throw new Error(payload.message);
    }

    if (! response.ok) {
        const firstValidationError = payload.errors ? Object.values(payload.errors).flat()[0] : null;
        throw new Error(firstValidationError ?? payload.message ?? 'The request could not be completed.');
    }

    return payload;
}

function openMenu() {
    if (! mobileNav || ! navOverlay || ! menuToggle) return;
    previousFocus = document.activeElement;
    mobileNav.inert = false;
    mobileNav.classList.add('is-open');
    mobileNav.setAttribute('aria-hidden', 'false');
    menuToggle.setAttribute('aria-expanded', 'true');
    navOverlay.hidden = false;
    document.body.classList.add('nav-open');
    menuClose?.focus();
}

function closeMenu({ restoreFocus = true } = {}) {
    if (! mobileNav || ! navOverlay || ! menuToggle) return;
    mobileNav.classList.remove('is-open');
    mobileNav.setAttribute('aria-hidden', 'true');
    mobileNav.inert = true;
    menuToggle.setAttribute('aria-expanded', 'false');
    navOverlay.hidden = true;
    document.body.classList.remove('nav-open');
    if (restoreFocus && previousFocus instanceof HTMLElement) previousFocus.focus();
    else if (mobileNav.contains(document.activeElement)) document.querySelector('.desktop-nav a')?.focus();
    previousFocus = null;
}

function renderSuggestions(payload, targetPanel = searchPanel, targetInput = searchInput) {
    if (! targetPanel || ! targetInput) return;

    if (! payload.suggestions.length) {
        targetPanel.innerHTML = '<div class="search-empty">No products found.</div>';
    } else {
        targetPanel.innerHTML = payload.suggestions.map((product) => `
            <a class="search-item" href="${escapeHtml(product.url)}">
                <img src="${escapeHtml(product.image)}" alt="">
                <span><strong>${escapeHtml(product.name)}</strong><small>${escapeHtml(product.category)}</small></span>
                <span>₱${Number(product.price).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</span>
            </a>
        `).join('');
    }

    targetPanel.hidden = false;
}

async function runSearch({ showPanel = true, sourceInput = searchInput, targetPanel = searchPanel } = {}) {
    if (! searchEndpoint) return;
    const query = sourceInput?.value.trim() ?? '';
    const sort = document.querySelector('[data-sort-select]')?.value ?? 'default';
    const productGrid = document.querySelector('#productGrid');
    const liveSection = document.querySelector('#liveSearchSection');
    const liveGrid = document.querySelector('#liveSearchGrid');
    const parameters = new URLSearchParams({ q: query, sort });

    if (showPanel && targetPanel) {
        targetPanel.hidden = false;
        targetPanel.innerHTML = '<div class="search-loading">Searching the atelier…</div>';
    }
    [productGrid, liveGrid].forEach((element) => element?.setAttribute('aria-busy', 'true'));
    productGrid?.classList.add('is-loading');

    try {
        const payload = await requestJson(`${searchEndpoint}?${parameters.toString()}`, { method: 'GET' });
        if (showPanel) renderSuggestions(payload, targetPanel, sourceInput);

        if (productGrid) {
            productGrid.innerHTML = payload.html;
            document.querySelector('#productCount').textContent = payload.count;
            shopCarouselController?.refresh({ reset: true });
        }

        if (liveSection && liveGrid) {
            liveSection.hidden = query === '';
            if (query !== '') {
                liveGrid.innerHTML = payload.html;
                document.querySelector('#liveSearchSummary').textContent = payload.message ?? `${payload.count} fragrance${payload.count === 1 ? '' : 's'} found for “${query}”.`;
            }
        }
    } catch (error) {
        showToast(error.message, 'error');
        if (targetPanel) targetPanel.innerHTML = '<div class="search-empty">Search is temporarily unavailable.</div>';
    } finally {
        [productGrid, liveGrid].forEach((element) => element?.setAttribute('aria-busy', 'false'));
        productGrid?.classList.remove('is-loading');
    }
}

function createProductCarousel(root) {
    const viewport = root?.querySelector('[data-carousel-viewport]');
    const track = root?.querySelector('#productGrid');
    const previousButton = root?.querySelector('[data-carousel-previous]');
    const nextButton = root?.querySelector('[data-carousel-next]');
    const pagination = root?.querySelector('[data-carousel-pagination]');
    const status = root?.querySelector('[data-carousel-status]');
    const announcement = root?.querySelector('[data-carousel-announcement]');

    if (! root || ! viewport || ! track || ! previousButton || ! nextButton || ! pagination) return null;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let pageOffsets = [0];
    let currentPage = 0;
    let scrollFrame;
    let resizeFrame;

    function cardsPerPage() {
        if (window.matchMedia('(max-width: 640px)').matches) return 1;
        if (window.matchMedia('(max-width: 980px)').matches) return 2;

        return 3;
    }

    function formatPageNumber(number) {
        return String(number).padStart(2, '0');
    }

    function renderState({ announce = false } = {}) {
        const pageCount = pageOffsets.length;
        const hasProducts = track.querySelectorAll('[data-product-card]').length > 0;
        const hasMultiplePages = hasProducts && pageCount > 1;

        previousButton.disabled = ! hasMultiplePages || currentPage === 0;
        nextButton.disabled = ! hasMultiplePages || currentPage === pageCount - 1;

        pagination.querySelectorAll('[data-carousel-page]').forEach((dot, index) => {
            const isCurrent = index === currentPage;
            dot.classList.toggle('is-current', isCurrent);
            dot.setAttribute('aria-current', isCurrent ? 'true' : 'false');
        });

        if (status) status.textContent = `${formatPageNumber(currentPage + 1)} / ${formatPageNumber(pageCount)}`;
        if (announce && announcement) announcement.textContent = `Page ${currentPage + 1} of ${pageCount}`;
    }

    function buildPagination() {
        pagination.replaceChildren();

        pageOffsets.forEach((offset, index) => {
            const dot = document.createElement('button');
            dot.className = 'shop-carousel-dot';
            dot.type = 'button';
            dot.dataset.carouselPage = String(index);
            dot.setAttribute('aria-label', `Go to carousel page ${index + 1}`);
            dot.addEventListener('click', () => goToPage(index));
            pagination.append(dot);
        });
    }

    function nearestPage() {
        return pageOffsets.reduce((nearest, offset, index) => (
            Math.abs(viewport.scrollLeft - offset) < Math.abs(viewport.scrollLeft - pageOffsets[nearest]) ? index : nearest
        ), 0);
    }

    function updateFromScroll() {
        window.cancelAnimationFrame(scrollFrame);
        scrollFrame = window.requestAnimationFrame(() => {
            const nextPage = nearestPage();
            if (nextPage !== currentPage) {
                currentPage = nextPage;
                renderState();
            }
        });
    }

    function goToPage(pageIndex, { immediate = false } = {}) {
        currentPage = Math.max(0, Math.min(pageIndex, pageOffsets.length - 1));
        if (immediate) {
            const inlineScrollBehavior = viewport.style.scrollBehavior;
            viewport.style.scrollBehavior = 'auto';
            viewport.scrollLeft = pageOffsets[currentPage];
            viewport.style.scrollBehavior = inlineScrollBehavior;
        } else {
            viewport.scrollTo({
                left: pageOffsets[currentPage],
                behavior: reducedMotion.matches ? 'auto' : 'smooth',
            });
        }
        renderState({ announce: true });
    }

    function refresh({ reset = false } = {}) {
        window.requestAnimationFrame(() => {
            const cards = [...track.querySelectorAll('[data-product-card]')];
            const isEmpty = cards.length === 0;
            const visibleCards = cardsPerPage();
            const maxScroll = Math.max(0, viewport.scrollWidth - viewport.clientWidth);
            const pageCount = Math.max(1, Math.ceil(cards.length / visibleCards));

            root.classList.toggle('is-empty', isEmpty);
            pageOffsets = Array.from({ length: pageCount }, (_, index) => {
                const cardIndex = Math.min(index * visibleCards, Math.max(0, cards.length - visibleCards));
                return Math.min(cards[cardIndex]?.offsetLeft ?? 0, maxScroll);
            });

            pageOffsets = pageOffsets.filter((offset, index, offsets) => index === 0 || Math.abs(offset - offsets[index - 1]) > 1);
            if (! pageOffsets.length) pageOffsets = [0];

            buildPagination();
            goToPage(reset || isEmpty ? 0 : nearestPage(), { immediate: true });
        });
    }

    previousButton.addEventListener('click', () => goToPage(currentPage - 1));
    nextButton.addEventListener('click', () => goToPage(currentPage + 1));
    viewport.addEventListener('scroll', updateFromScroll, { passive: true });
    viewport.addEventListener('keydown', (event) => {
        if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
        event.preventDefault();
        goToPage(currentPage + (event.key === 'ArrowRight' ? 1 : -1));
    });
    window.addEventListener('resize', () => {
        window.cancelAnimationFrame(resizeFrame);
        resizeFrame = window.requestAnimationFrame(() => refresh());
    });

    refresh({ reset: true });

    return { refresh };
}

async function addToCart(button) {
    const detailQuantity = button.hasAttribute('data-detail-quantity')
        ? Number(document.querySelector('#detailQuantity')?.value ?? 1)
        : 1;
    button.disabled = true;

    try {
        const payload = await requestJson(button.dataset.addCart, {
            method: 'POST', body: JSON.stringify({ quantity: detailQuantity }),
        });
        updateCounts('[data-cart-count]', payload.cart_count);
        const cartPanel = document.querySelector('#cartPanel');
        if (cartPanel && payload.html) cartPanel.innerHTML = payload.html;
        showToast(payload.message);
    } catch (error) {
        showToast(error.message, 'error');
    } finally {
        button.disabled = false;
    }
}

async function toggleWishlist(button) {
    button.disabled = true;
    try {
        const payload = await requestJson(button.dataset.toggleWishlist, { method: 'POST', body: '{}' });
        updateCounts('[data-wishlist-count]', payload.wishlist_count);
        document.querySelectorAll(`[data-toggle-wishlist="${CSS.escape(button.dataset.toggleWishlist)}"]`).forEach((control) => {
            control.classList.toggle('is-active', payload.active);
            control.setAttribute('aria-pressed', String(payload.active));
            if (control.classList.contains('wish-detail')) control.textContent = payload.active ? 'Remove Wishlist' : 'Add to Wishlist';
        });
        const wishlistPanel = document.querySelector('#wishlistPanel');
        if (wishlistPanel && payload.html) wishlistPanel.innerHTML = payload.html;
        showToast(payload.message);
    } catch (error) {
        showToast(error.message, 'error');
    } finally {
        button.disabled = false;
    }
}

async function updateCart(control, quantity) {
    const panel = document.querySelector('#cartPanel');
    panel?.setAttribute('aria-busy', 'true');
    try {
        const payload = await requestJson(control.dataset.updateUrl, {
            method: 'PATCH', body: JSON.stringify({ quantity }),
        });
        updateCounts('[data-cart-count]', payload.cart_count);
        if (panel) panel.innerHTML = payload.html;
        showToast(payload.message);
    } catch (error) {
        showToast(error.message, 'error');
    } finally {
        panel?.setAttribute('aria-busy', 'false');
    }
}

async function removeItem(button, type) {
    const panel = document.querySelector(type === 'cart' ? '#cartPanel' : '#wishlistPanel');
    panel?.setAttribute('aria-busy', 'true');
    try {
        const url = type === 'cart' ? button.dataset.removeCart : button.dataset.removeWishlist;
        const payload = await requestJson(url, { method: 'DELETE' });
        if (type === 'cart') updateCounts('[data-cart-count]', payload.cart_count);
        else updateCounts('[data-wishlist-count]', payload.wishlist_count);
        if (panel) panel.innerHTML = payload.html;
        showToast(payload.message);
    } catch (error) {
        showToast(error.message, 'error');
    } finally {
        panel?.setAttribute('aria-busy', 'false');
    }
}

menuToggle?.addEventListener('click', () => mobileNav?.classList.contains('is-open') ? closeMenu() : openMenu());
menuClose?.addEventListener('click', closeMenu);
navOverlay?.addEventListener('click', closeMenu);
window.addEventListener('resize', () => {
    if (window.innerWidth > 1180 && mobileNav?.classList.contains('is-open')) closeMenu({ restoreFocus: false });
});

searchForm?.addEventListener('submit', (event) => {
    event.preventDefault();
    runSearch({ showPanel: true });
});
searchInput?.addEventListener('input', () => {
    if (mobileSearch) mobileSearch.value = searchInput.value;
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => runSearch({ showPanel: true }), 280);
});
document.querySelector('#searchSubmit')?.addEventListener('click', (event) => {
    event.preventDefault();
    runSearch({ showPanel: true });
});
mobileSearchForm?.addEventListener('submit', (event) => {
    event.preventDefault();
    if (searchInput) searchInput.value = mobileSearch.value;
    runSearch({ showPanel: true, sourceInput: mobileSearch, targetPanel: mobileSearchPanel });
});
mobileSearch?.addEventListener('input', () => {
    if (searchInput) searchInput.value = mobileSearch.value;
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => runSearch({ showPanel: true, sourceInput: mobileSearch, targetPanel: mobileSearchPanel }), 280);
});

document.querySelector('[data-sort-select]')?.addEventListener('change', () => runSearch({ showPanel: false }));
shopCarouselController = createProductCarousel(document.querySelector('[data-product-carousel]'));
document.querySelector('#remember')?.addEventListener('change', (event) => {
    document.querySelector('#rememberStatus').textContent = event.target.checked ? 'Remembered session enabled.' : 'Normal session selected.';
});
document.querySelectorAll('[data-auth-input]').forEach((input) => input.addEventListener('input', () => {
    input.closest('.field')?.classList.remove('has-error');
}));
function validateLoginFields(form) {
    if (! form) return;
    const username = form.querySelector('#username');
    const password = form.querySelector('#password');
    [username, password].forEach((input) => input.closest('.field')?.classList.toggle('has-error', ! input.value.trim()));
}
document.querySelector('#loginForm')?.addEventListener('submit', (event) => validateLoginFields(event.currentTarget));
document.querySelector('#loginForm button[type="submit"]')?.addEventListener('click', () => validateLoginFields(document.querySelector('#loginForm')));
document.querySelector('#forgotPasswordLink')?.addEventListener('click', closeMenu);
document.querySelector('#contactForm')?.addEventListener('submit', (event) => {
    event.currentTarget.setAttribute('aria-busy', 'true');
    const live = event.currentTarget.querySelector('.form-live');
    if (live) live.textContent = 'Sending your message…';
});

document.addEventListener('click', (event) => {
    if (! event.target.closest('.header-search') && searchPanel && searchInput) {
        searchPanel.hidden = true;
    }
    if (event.target.closest('[data-nav-close]')) closeMenu();

    const addButton = event.target.closest('[data-add-cart]');
    if (addButton) {
        event.preventDefault();
        event.stopPropagation();
        addToCart(addButton);
        return;
    }

    const wishlistButton = event.target.closest('[data-toggle-wishlist]');
    if (wishlistButton) {
        event.preventDefault();
        event.stopPropagation();
        toggleWishlist(wishlistButton);
        return;
    }

    const cartControl = event.target.closest('[data-cart-quantity]');
    if (cartControl && (event.target.closest('[data-cart-plus]') || event.target.closest('[data-cart-minus]'))) {
        const current = Number(cartControl.querySelector('output').textContent);
        updateCart(cartControl, current + (event.target.closest('[data-cart-plus]') ? 1 : -1));
        return;
    }

    const cartRemove = event.target.closest('[data-remove-cart]');
    if (cartRemove) { removeItem(cartRemove, 'cart'); return; }
    const wishlistRemove = event.target.closest('[data-remove-wishlist]');
    if (wishlistRemove) { removeItem(wishlistRemove, 'wishlist'); return; }

    const minus = event.target.closest('[data-quantity-minus]');
    const plus = event.target.closest('[data-quantity-plus]');
    if (minus || plus) {
        const input = document.querySelector('#detailQuantity');
        if (! input) return;
        const minimum = Number(input.min || 1);
        const maximum = Number(input.max || Number.MAX_SAFE_INTEGER);
        input.value = String(Math.min(maximum, Math.max(minimum, Number(input.value || 1) + (plus ? 1 : -1))));
        input.dispatchEvent(new Event('input', { bubbles: true }));
        return;
    }

    const card = event.target.closest('[data-product-card]');
    if (card && ! event.target.closest('a,button,input,select,textarea')) window.location.assign(card.dataset.productUrl);
});

document.querySelectorAll('a[href]').forEach((link) => link.addEventListener('click', () => {
    if (mobileNav?.classList.contains('is-open')) closeMenu();
}));

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        if (mobileNav?.classList.contains('is-open')) closeMenu();
        if (searchPanel && searchInput) {
            searchPanel.hidden = true;
        }
    }
    const card = event.target.closest?.('[data-product-card]');
    if (card && (event.key === 'Enter' || event.key === ' ')) {
        event.preventDefault();
        window.location.assign(card.dataset.productUrl);
    }
});

document.querySelector('#detailQuantity')?.addEventListener('input', (event) => {
    const input = event.currentTarget;
    input.value = String(Math.min(Number(input.max), Math.max(Number(input.min), Number(input.value || 1))));
});
