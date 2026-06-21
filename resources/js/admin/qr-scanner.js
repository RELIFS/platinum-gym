// Lazy-loaded admin check-in QR camera scanner.
// Uses html5-qrcode (dynamic import) so that the main app bundle is not inflated for non-admin pages.
// IMPORTANT: getUserMedia requires a secure context (HTTPS or http://localhost). When the page is
// served over plain HTTP on a production domain (e.g. before SSL is provisioned), camera access
// will be blocked by the browser. We surface that with a clear banner and keep check-in QR-only.

const CAMERA_REGION_ID = 'admin-qr-camera-region';

function showToast(message, kind = 'success') {
    const toast = document.createElement('div');
    const palette = kind === 'error'
        ? 'border-red-500/40 bg-red-500/10 text-red-700 dark:text-red-200'
        : 'border-emerald-500/40 bg-emerald-500/10 text-emerald-700 dark:text-emerald-200';
    toast.className = `pointer-events-none fixed inset-x-0 top-4 z-[60] mx-auto w-fit max-w-[min(90vw,28rem)] rounded-lg border ${palette} px-4 py-2 text-sm font-bold shadow-lg`;
    toast.setAttribute('role', kind === 'error' ? 'alert' : 'status');
    toast.setAttribute('aria-live', kind === 'error' ? 'assertive' : 'polite');
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function submitTokenForm(token) {
    const form = document.getElementById('admin-qr-scan-form');
    if (!form) return false;
    const input = form.querySelector('input[name="token"]');
    if (!input) return false;
    input.value = token;
    // Mark a flag so the user knows the page is about to reload with a status.
    showToast('QR terdeteksi. Memuat preview member...', 'success');
    form.submit();
    return true;
}

export async function initAdminQrScanner() {
    const startBtn = document.getElementById('admin-qr-camera-start');
    const stopBtn = document.getElementById('admin-qr-camera-stop');
    const region = document.getElementById(CAMERA_REGION_ID);
    const secureBanner = document.getElementById('admin-qr-camera-secure-banner');
    const supportBanner = document.getElementById('admin-qr-camera-support-banner');

    if (!startBtn || !region) return;

    if (!window.isSecureContext) {
        startBtn.disabled = true;
        startBtn.setAttribute('aria-disabled', 'true');
        if (secureBanner) secureBanner.classList.remove('hidden');
        return;
    }

    if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
        startBtn.disabled = true;
        startBtn.setAttribute('aria-disabled', 'true');
        if (supportBanner) supportBanner.classList.remove('hidden');
        return;
    }

    let scanner = null;
    let isScanning = false;

    startBtn.addEventListener('click', async () => {
        if (isScanning) return;
        startBtn.disabled = true;
        startBtn.setAttribute('aria-busy', 'true');
        try {
            const { Html5Qrcode } = await import('html5-qrcode');
            scanner = new Html5Qrcode(CAMERA_REGION_ID, { verbose: false });
            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 240, height: 240 } },
                (decodedText) => {
                    if (!decodedText || isScanning === false) return;
                    isScanning = false;
                    scanner.stop().catch(() => {}).finally(() => {
                        submitTokenForm(decodedText.trim());
                    });
                },
                () => { /* per-frame failure: ignored */ },
            );
            isScanning = true;
            if (stopBtn) {
                stopBtn.classList.remove('hidden');
                stopBtn.disabled = false;
            }
            startBtn.classList.add('hidden');
        } catch (error) {
            console.error('[admin-qr] camera start failed', error);
            showToast('Tidak bisa mengakses kamera. Pastikan browser mendukung kamera dan koneksi aman.', 'error');
            startBtn.disabled = false;
            startBtn.removeAttribute('aria-busy');
        }
    });

    if (stopBtn) {
        stopBtn.addEventListener('click', async () => {
            if (!scanner || !isScanning) return;
            try {
                await scanner.stop();
                isScanning = false;
                stopBtn.classList.add('hidden');
                startBtn.classList.remove('hidden');
                startBtn.disabled = false;
                startBtn.removeAttribute('aria-busy');
            } catch (error) {
                console.error('[admin-qr] camera stop failed', error);
            }
        });
    }
}
