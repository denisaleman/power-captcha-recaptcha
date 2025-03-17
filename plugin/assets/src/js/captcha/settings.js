export function getSettings() {
    if (!window.pwrcap) {
        throw new Error(
            'Power Captcha reCAPTCHA Error: pwrcap is undefined.'
        );
    }

    return window.pwrcap;
}

const settings = getSettings();

if (settings.debug) {
    console.log(settings);
}

export default settings;