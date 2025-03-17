(($) => {
    $(() => {
        const $captchaV2Fieldset = $('#fieldset-captcha-v2-type');
        const $captchaTypeRadio = $('.captcha-type-radio');

        $captchaTypeRadio.on('change', function () {
            $captchaV2Fieldset.toggle(
                $(this).filter(':checked').val() === 'v2'
            );
        });
    });
})(jQuery);