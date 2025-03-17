import settings from './settings';
import { cf7VerifyCallback } from './cf7';

function render($container, fitToContainer = false) {
    const holderId = grecaptcha.render($container, {
        sitekey: settings.site_key,
        callback() {
            cf7VerifyCallback($container, holderId);
        },
    });

    if (fitToContainer) {
        const scale =
            $container.clientWidth / $container.children[0].clientWidth;

        $container.style.transform = `scale(${scale})`;
    }
}

export function initV2Checkbox() {
    document.querySelectorAll('.pwrcap-wrapper').forEach((wrapper) => {
        const fit = [
            'login_form',
            'register_form',
            'lostpassword_form',
            'resetpass_form',
        ].includes(wrapper.dataset.context);

        render(wrapper, fit);
    });
}