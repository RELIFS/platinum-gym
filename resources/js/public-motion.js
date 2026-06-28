const ROOT_SELECTOR = '[data-public-motion-root]';
const REVEAL_SELECTOR = '[data-motion~="reveal"]';
const DEPTH_SELECTOR = '[data-motion~="depth"]';
const REDUCED_MOTION_QUERY = '(prefers-reduced-motion: reduce)';
const FINE_POINTER_QUERY = '(hover: hover) and (pointer: fine)';
const DEPTH_LERP = 0.16;
const DEPTH_SETTLE_THRESHOLD = 0.025;

const clamp = (value, min, max) => Math.min(Math.max(value, min), max);
const reducedMotionMedia = window.matchMedia(REDUCED_MOTION_QUERY);
const finePointerMedia = window.matchMedia(FINE_POINTER_QUERY);

const prefersReducedMotion = () => reducedMotionMedia.matches;
const supportsFinePointer = () => finePointerMedia.matches;

const revealElement = (element) => {
    element.classList.add('public-motion-visible');
};

const resetDepthElement = (element) => {
    element.style.setProperty('--motion-depth-x', '0deg');
    element.style.setProperty('--motion-depth-y', '0deg');
    element.style.setProperty('--motion-depth-shift-x', '0px');
    element.style.setProperty('--motion-depth-shift-y', '0px');
};

const setRevealDelay = (element) => {
    const delay = Number.parseInt(element.dataset.motionDelay ?? '0', 10);

    if (Number.isFinite(delay) && delay > 0) {
        element.style.setProperty('--motion-delay', `${Math.min(delay, 180)}ms`);
    }
};

const initReveal = (root) => {
    const elements = Array.from(root.querySelectorAll(REVEAL_SELECTOR));

    root.classList.add('public-motion-ready');

    if (elements.length === 0) {
        return;
    }

    elements.forEach(setRevealDelay);

    if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
        elements.forEach(revealElement);

        return;
    }

    const initiallyVisible = [];
    const deferred = [];
    const viewportLimit = window.innerHeight * 0.94;

    elements.forEach((element) => {
        if (element.getBoundingClientRect().top < viewportLimit) {
            initiallyVisible.push(element);
        } else {
            deferred.push(element);
        }
    });

    initiallyVisible.forEach(revealElement);

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                revealElement(entry.target);
                observer.unobserve(entry.target);
            });
        },
        { rootMargin: '0px 0px -10% 0px', threshold: 0.1 },
    );

    deferred.forEach((element) => observer.observe(element));
};

const makeDepthItem = (element) => {
    const state = {
        active: false,
        bounds: null,
        frame: 0,
        current: { rotateX: 0, rotateY: 0, shiftX: 0, shiftY: 0 },
        target: { rotateX: 0, rotateY: 0, shiftX: 0, shiftY: 0 },
    };

    const applyDepth = () => {
        element.style.setProperty('--motion-depth-x', `${state.current.rotateX.toFixed(2)}deg`);
        element.style.setProperty('--motion-depth-y', `${state.current.rotateY.toFixed(2)}deg`);
        element.style.setProperty('--motion-depth-shift-x', `${state.current.shiftX.toFixed(2)}px`);
        element.style.setProperty('--motion-depth-shift-y', `${state.current.shiftY.toFixed(2)}px`);
    };

    const hasSettled = () => Object.keys(state.current).every((key) => (
        Math.abs(state.current[key] - state.target[key]) < DEPTH_SETTLE_THRESHOLD
    ));

    const step = () => {
        Object.keys(state.current).forEach((key) => {
            state.current[key] += (state.target[key] - state.current[key]) * DEPTH_LERP;
        });

        if (hasSettled()) {
            Object.assign(state.current, state.target);
        }

        applyDepth();

        if (state.active || !hasSettled()) {
            state.frame = window.requestAnimationFrame(step);

            return;
        }

        state.frame = 0;
    };

    const ensureFrame = () => {
        if (!state.frame) {
            state.frame = window.requestAnimationFrame(step);
        }
    };

    const resetTarget = () => {
        state.bounds = null;
        state.active = false;
        Object.assign(state.target, { rotateX: 0, rotateY: 0, shiftX: 0, shiftY: 0 });
        ensureFrame();
    };

    const prime = () => {
        state.bounds = element.getBoundingClientRect();
        state.active = true;
    };

    const update = (event) => {
        if (!state.bounds) {
            state.bounds = element.getBoundingClientRect();
        }

        if (state.bounds.width === 0 || state.bounds.height === 0) {
            resetTarget();

            return;
        }

        const x = (event.clientX - state.bounds.left) / state.bounds.width - 0.5;
        const y = (event.clientY - state.bounds.top) / state.bounds.height - 0.5;

        state.active = true;
        state.target.rotateX = clamp(y * -2.6, -2.6, 2.6);
        state.target.rotateY = clamp(x * 3.4, -3.4, 3.4);
        state.target.shiftX = clamp(x * 5, -5, 5);
        state.target.shiftY = clamp(y * 4, -4, 4);
        ensureFrame();
    };

    const cleanup = () => {
        element.removeEventListener('pointerenter', prime);
        element.removeEventListener('pointermove', update);
        element.removeEventListener('pointerleave', resetTarget);

        if (state.frame) {
            window.cancelAnimationFrame(state.frame);
            state.frame = 0;
        }

        resetDepthElement(element);
    };

    element.addEventListener('pointerenter', prime);
    element.addEventListener('pointermove', update);
    element.addEventListener('pointerleave', resetTarget);

    return { cleanup, reset: resetTarget };
};

const initDepth = (root) => {
    const items = Array.from(root.querySelectorAll(DEPTH_SELECTOR)).map(makeDepthItem);
    const handleResize = () => items.forEach((item) => item.reset());

    window.addEventListener('resize', handleResize, { passive: true });

    return () => {
        window.removeEventListener('resize', handleResize);
        items.forEach((item) => item.cleanup());
    };
};

export function initPublicMotion() {
    const root = document.querySelector(ROOT_SELECTOR);

    if (!root || root.dataset.publicMotionReady === 'true') {
        return;
    }

    root.dataset.publicMotionReady = 'true';

    initReveal(root);

    let cleanupDepth = null;

    const revealAll = () => {
        root.querySelectorAll(REVEAL_SELECTOR).forEach(revealElement);
    };

    const syncDepth = () => {
        if (cleanupDepth) {
            cleanupDepth();
            cleanupDepth = null;
        }

        if (!prefersReducedMotion() && supportsFinePointer()) {
            cleanupDepth = initDepth(root);

            return;
        }

        root.querySelectorAll(DEPTH_SELECTOR).forEach(resetDepthElement);
    };

    const handleMotionPreferenceChange = () => {
        if (prefersReducedMotion()) {
            revealAll();
        }

        syncDepth();
    };

    syncDepth();
    reducedMotionMedia.addEventListener('change', handleMotionPreferenceChange);
    finePointerMedia.addEventListener('change', syncDepth);
}
