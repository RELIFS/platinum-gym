const DESKTOP_QUERY = '(min-width: 1024px)';
const HIDE_DELTA = 12;
const TOP_OFFSET = 4;

const mobileMenuSources = new Set();

let topbars = [];
let lastScrollY = window.scrollY;
let downDistance = 0;
let ticking = false;
let desktopQuery = null;

function ensureUiBridge() {
    window.platinumGymUi = {
        ...(window.platinumGymUi ?? {}),
        setMobileMenuOpen,
    };
}

export function setMobileMenuOpen(source = 'global', open = false) {
    if (open) {
        mobileMenuSources.add(source);
    } else {
        mobileMenuSources.delete(source);
    }

    const isOpen = mobileMenuSources.size > 0;
    document.documentElement.toggleAttribute('data-mobile-menu-open', isOpen);
    window.dispatchEvent(new CustomEvent('platinum-gym:mobile-menu-change', { detail: { open: isOpen } }));
    requestTopbarUpdate();
}

export function initAutoHideTopbars() {
    ensureUiBridge();

    topbars = Array.from(document.querySelectorAll('[data-auto-hide-topbar]')).map((element) => ({
        element,
        height: element.offsetHeight || 0,
        scope: element.dataset.autoHideScope ?? 'all',
    }));

    if (!topbars.length) {
        return;
    }

    desktopQuery = window.matchMedia(DESKTOP_QUERY);

    topbars.forEach(({ element }) => {
        element.dataset.topbarReady = 'true';
        element.addEventListener('focusin', () => showTopbar(element));
        element.addEventListener('pointerenter', () => showTopbar(element));
    });

    window.addEventListener('scroll', requestTopbarUpdate, { passive: true });
    window.addEventListener('resize', () => {
        refreshTopbarMetrics();
        requestTopbarUpdate();
    }, { passive: true });
    window.addEventListener('platinum-gym:mobile-menu-change', requestTopbarUpdate);
    desktopQuery.addEventListener?.('change', requestTopbarUpdate);

    new MutationObserver(requestTopbarUpdate).observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-mobile-menu-open'],
    });

    refreshTopbarMetrics();
    updateTopbars();
}

function refreshTopbarMetrics() {
    topbars = topbars.map((topbar) => ({
        ...topbar,
        height: topbar.element.offsetHeight || topbar.height,
    }));
}

function requestTopbarUpdate() {
    if (ticking) {
        return;
    }

    ticking = true;
    window.requestAnimationFrame(updateTopbars);
}

function updateTopbars() {
    const currentScrollY = Math.max(window.scrollY, 0);
    const delta = currentScrollY - lastScrollY;

    if (delta > 0) {
        downDistance += delta;
    } else if (delta < 0) {
        downDistance = 0;
    }

    topbars.forEach((topbar) => {
        if (mustStayVisible(topbar, currentScrollY)) {
            showTopbar(topbar.element);
            resetScrollDistances();

            return;
        }

        const hideThreshold = Math.max(96, topbar.height);

        if (delta < 0 && isTopbarHidden(topbar.element)) {
            showTopbar(topbar.element);
            resetScrollDistances();

            return;
        }

        if (downDistance >= HIDE_DELTA && currentScrollY > hideThreshold) {
            hideTopbar(topbar.element);

            return;
        }

        if (delta < 0) {
            showTopbar(topbar.element);
            resetScrollDistances();
        }
    });

    lastScrollY = currentScrollY;
    ticking = false;
}

function mustStayVisible(topbar, currentScrollY) {
    if (currentScrollY <= TOP_OFFSET || document.documentElement.hasAttribute('data-mobile-menu-open')) {
        return true;
    }

    if (topbar.scope === 'below-lg' && desktopQuery?.matches) {
        return true;
    }

    return topbar.element.contains(document.activeElement);
}

function showTopbar(element) {
    element.removeAttribute('data-topbar-hidden');
}

function hideTopbar(element) {
    element.setAttribute('data-topbar-hidden', 'true');
}

function isTopbarHidden(element) {
    return element.hasAttribute('data-topbar-hidden');
}

function resetScrollDistances() {
    downDistance = 0;
}

ensureUiBridge();
