jQuery(function($) {
	function bbp_reply_ajax_call( action, nonce, form_data ) {
		var $data = {
			action    : action,
			nonce     : nonce,
			form_data : form_data
		};

		$.post( bbpReplyAjaxJS.bbp_ajaxurl, $data, function ( response ) {
			if ( response.success ) {
				$( update_selector ).html( response.content );
			} else {
				if ( !response.content ) {
					response.content = bbpReplyAjaxJS.generic_ajax_error;
				}
				alert( response.content );
			}
		} );
	}

	$( '.bbp-reply-form' ).on( 'click', '#bbp_reply_submit', function( e ) {
		e.preventDefault();
		//bbp_reply_ajax_call( 'reply', bbpReplyAjaxJS.reply_nonce );
	} );
});