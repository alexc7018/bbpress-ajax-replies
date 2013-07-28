jQuery(function($) {
	function bbp_reply_ajax_call( action, nonce, form_data ) {
		var $data = {
			action : action,
			nonce  : nonce
		};

		$.each(form_data, function(i, field){
			if ( field.name == "action" ) {
				$data["bbp_reply_form_action"] = field.value;
			} else {
				$data[field.name] = field.value;
			}
		});

		$.post( bbpReplyAjaxJS.bbp_ajaxurl, $data, function ( response ) {
			if ( response.success ) {
				$('.bbp-footer').before( '<li>' + response.content + '</li>' );
			} else {
				console.log(response);
				if ( !response.content ) {
					response.content = bbpReplyAjaxJS.generic_ajax_error;
				}
				alert( response.content );
			}
		} );
	}

	$( '.bbp-reply-form form' ).submit( function( e ) {
		e.preventDefault();
		bbp_reply_ajax_call( 'reply', bbpReplyAjaxJS.reply_nonce, $(this).serializeArray() );
	} );
});