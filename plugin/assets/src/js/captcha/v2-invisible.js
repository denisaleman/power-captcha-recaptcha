import settings from './settings';

export function initV2Invisible() {
    document.querySelectorAll('form').forEach((form) => {
        const wrapper = form.querySelector('.pwrcap-wrapper');

        if (!wrapper) {
            return;
        }

        const holderId = grecaptcha.render(wrapper, {
            sitekey: settings.site_key,
            size: 'invisible',
            badge: 'bottomright',

            callback() {
                HTMLFormElement.prototype.submit.call(form);
            },
        });

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            grecaptcha.execute(holderId);
        });
    });
}