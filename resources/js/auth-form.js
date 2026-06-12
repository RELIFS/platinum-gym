export function initAuthFormFeedback() {
    bindPasswordToggles();
    bindPasswordFeedback();
    bindPhoneFeedback();
}

function bindPasswordToggles() {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        if (button.dataset.authBound === 'true') {
            return;
        }

        button.dataset.authBound = 'true';
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);

            if (!input) {
                return;
            }

            const shouldShow = input.type === 'password';
            input.type = shouldShow ? 'text' : 'password';
            button.setAttribute('aria-label', shouldShow ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            button.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
            button.querySelector('[data-eye-open]')?.classList.toggle('hidden', shouldShow);
            button.querySelector('[data-eye-closed]')?.classList.toggle('hidden', !shouldShow);
        });
    });
}

function bindPasswordFeedback() {
    document.querySelectorAll('[data-password-feedback-input]').forEach((input) => {
        if (input.dataset.authFeedbackBound === 'true') {
            return;
        }

        const feedback = document.getElementById(`${input.id}-feedback`);

        if (!feedback) {
            return;
        }

        input.dataset.authFeedbackBound = 'true';
        feedback.setAttribute('role', 'status');
        feedback.setAttribute('aria-live', 'polite');

        const updatePasswordFeedback = () => {
            const shouldShow = input.value.length > 0 && input.value.length < 8;
            feedback.classList.toggle('hidden', !shouldShow);
        };

        input.addEventListener('input', updatePasswordFeedback);
        input.addEventListener('blur', updatePasswordFeedback);
        updatePasswordFeedback();
    });
}

function bindPhoneFeedback() {
    document.querySelectorAll('[data-phone-feedback-input]').forEach((input) => {
        if (input.dataset.authFeedbackBound === 'true') {
            return;
        }

        const feedback = document.getElementById(`${input.id}-feedback`);
        const phonePattern = /^08\d{8,12}$/;

        if (!feedback) {
            return;
        }

        input.dataset.authFeedbackBound = 'true';
        feedback.setAttribute('role', 'status');
        feedback.setAttribute('aria-live', 'polite');

        const updatePhoneFeedback = () => {
            const normalized = input.value.replace(/\D+/g, '');
            const shouldShow = normalized.length >= 2 && !phonePattern.test(normalized);
            feedback.classList.toggle('hidden', !shouldShow);
        };

        input.addEventListener('input', updatePhoneFeedback);
        input.addEventListener('blur', updatePhoneFeedback);
        updatePhoneFeedback();
    });
}
