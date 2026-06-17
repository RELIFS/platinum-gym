import './bootstrap';
import { initAuthFormFeedback } from './auth-form';
import { initPlatinumGymChatbots, platinumGymChatbot } from './public-chatbot';

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

Alpine.plugin(focus);

window.Alpine = Alpine;
window.platinumGymChatbot = platinumGymChatbot;

Alpine.start();
initAuthFormFeedback();
initPlatinumGymChatbots();

// Lazy-load the admin QR camera scanner only on the check-in page. Keeps the html5-qrcode
// bundle out of the public/member surfaces.
if (document.getElementById('admin-qr-camera-region')) {
    import('./admin/qr-scanner.js')
        .then((module) => module.initAdminQrScanner())
        .catch((error) => console.error('[admin-qr] failed to load scanner module', error));
}
