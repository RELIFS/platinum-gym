import './bootstrap';
import { initAuthFormFeedback } from './auth-form';
import { registerAdminBookingForms } from './admin/booking-form';
import { registerMemberBookingForms } from './member/booking-form';
import { initPublicMotion } from './public-motion';
import { initPlatinumGymChatbots, platinumGymChatbot } from './public-chatbot';

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

Alpine.plugin(focus);

window.Alpine = Alpine;
window.platinumGymChatbot = platinumGymChatbot;

registerAdminBookingForms();
registerMemberBookingForms();
Alpine.start();
initAuthFormFeedback();
initPublicMotion();
initPlatinumGymChatbots();

// Lazy-load the admin QR camera scanner only on the check-in page. Keeps the html5-qrcode
// bundle out of the public/member surfaces.
if (document.getElementById('admin-qr-camera-region')) {
    import('./admin/qr-scanner.js')
        .then((module) => module.initAdminQrScanner())
        .catch((error) => console.error('[admin-qr] failed to load scanner module', error));
}

// Lazy-load the interactive admin dashboard chart only when the dashboard mount exists.
if (document.getElementById('admin-operational-trend-chart')) {
    import('./admin/operational-trend-chart.js')
        .then((module) => module.initAdminOperationalTrendChart())
        .catch((error) => console.error('[admin-trend] failed to load chart module', error));
}

// Lazy-load the owner business chart only on the owner dashboard.
if (document.getElementById('owner-business-trend-chart')) {
    import('./owner/business-trend-chart.js')
        .then((module) => module.initOwnerBusinessTrendChart())
        .catch((error) => console.error('[owner-trend] failed to load chart module', error));
}
