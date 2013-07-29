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
				var reply_list_item = '<li>' + response.content + '</li>';

				if ( 'edit' == response.reply_type ) {
					// in-place editing doesn't work yet, but could (and should) eventually
					$('#post-' + response.reply_id).parent('li').replaceWith(reply_list_item);
				} else {
					if ( response.reply_parent && response.reply_parent != response.reply_id ) {
						// threaded comment
						var $parent = $('#post-' + response.reply_parent).parent('li');
						var list_type = 'ul';
						if ( $('.bbp-replies').is('ol') ) {
							list_type = 'ol';
						}
						if ( 0 == $parent.next(list_type).length ) {
							$parent.after('<' + list_type + ' class="bbp-threaded-replies"></' + list_type + '>');
						}
						$parent.next(list_type).append(reply_list_item)
					} else {
						$('.bbp-footer').before(reply_list_item);
					}
				}
				reset_reply_form();
				var $new_element = $('.post-' + response.reply_id);
				$new_element.removeClass('odd').addClass('even');
				var orig_color = $new_element.css('backgroundColor');
				$new_element.css('backgroundColor', 'lightYellow').animate({backgroundColor: orig_color}, 2000);
			} else {
				console.log(response);
				if ( !response.content ) {
					response.content = bbpReplyAjaxJS.generic_ajax_error;
				}
				alert( response.content );
			}
		} );
	}

	function reset_reply_form() {
		$('#bbp-cancel-reply-to-link').trigger('click');
		$('#bbp_reply_content').val('');
	}

	$( '.bbp-reply-form form' ).on( "submit", function( e ) {
		e.preventDefault();
		bbp_reply_ajax_call( 'reply', bbpReplyAjaxJS.reply_nonce, $(this).serializeArray() );
	} );
});