export function cf7VerifyCallback(container) {
    const formControl = container.closest('.wpcf7-form-control');

    formControl
        ?.querySelector('.wpcf7-not-valid-tip')
        ?.remove();
}