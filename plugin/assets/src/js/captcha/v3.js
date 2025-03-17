import settings from './settings';

export function initV3() {
    grecaptcha
        .execute(settings.site_key, {
            action: 'validate_recaptchav3',
        })
        .then((token) => {
            document
                .querySelectorAll('.g-recaptcha-response')
                .forEach((element) => {
                    element.value = token;
                });
        });
}