(function ($) {
	$(document).ready(function () {
		$(document).on( 'click', '.pwrcap-notice-not-configured .notice-dismiss', function () {
			var nonce = $( this ).closest( '.pwrcap-notice-not-configured' ).data( 'nonce' );
			var action = $( this ).closest( '.pwrcap-notice-not-configured' ).data( 'action' );

			$.ajax(ajaxurl, {
				type: 'POST',
				data: {
					action: action,
					nonce: nonce,
				}
			});
		});
	});
})(jQuery);