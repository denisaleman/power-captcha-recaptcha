(($) => {
    $(() => {
        $(document).on(
            'click',
            '.pwrcap-notice-not-configured .notice-dismiss',
            function () {
                const $notice = $(this).closest('.pwrcap-notice-not-configured');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: $notice.data('action'),
                        nonce: $notice.data('nonce'),
                    },
                });
            }
        );
    });
})(jQuery);