import './bootstrap';
import { initAuthFormFeedback } from './auth-form';
import { platinumGymChatbot } from './public-chatbot';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
window.platinumGymChatbot = platinumGymChatbot;

Alpine.start();
initAuthFormFeedback();
