<?php
/*
 * Plugin Name: bbPress AJAX Replies
 * Plugin URI:  http://wordpress.org/plugins/bbpress-ajax-replies/
 * Description: Gives topic replies in bbPress more ajaxy goodness.
 * Author:      Alison Barrett
 * Author URI:  http://alisothegeek.com/
 * Version:     1.0
 */

class bbPress_Ajax_Replies {

	private $url;

	function __construct() {
		$this->setup_globals();
		$this->setup_actions();
	}

	private function setup_globals() {
		$this->url = trailingslashit( plugins_url( basename( __DIR__ ) ) );
	}

	private function setup_actions() {
		add_action( 'bbp_enqueue_scripts', array( $this, 'enqueue_scripts'            ) );
		add_action( 'bbp_enqueue_scripts', array( $this, 'localize_reply_ajax_script' ) );
		add_action( 'bbp_ajax_reply',      array( $this, 'ajax_reply'                 ) );
		add_action( 'bbp_new_reply_post_extras', array( $this, 'reply_post_extras' ), 99 );
		add_action( 'bbp_edit_reply_post_extras', array( $this, 'reply_post_extras' ), 99 );
	}

	public function enqueue_scripts() {
		if ( bbp_is_single_topic() ) {
			wp_enqueue_script( 'bbpress-reply-ajax', $this->url . 'reply-ajax.js', array( 'bbpress-topic', 'bbpress-reply', 'jquery' ) );
		}
	}

	public function localize_reply_ajax_script() {
		// Bail if not viewing a single topic
		if ( !bbp_is_single_topic() )
			return;

		wp_localize_script( 'bbpress-reply-ajax', 'bbpReplyAjaxJS', array(
			'bbp_ajaxurl'        => bbp_get_ajax_url(),
			'generic_ajax_error' => __( 'Something went wrong. Refresh your browser and try again.', 'bbpress' ),
			'is_user_logged_in'  => is_user_logged_in(),
			'reply_nonce'        => wp_create_nonce( 'reply-ajax_' .     get_the_ID() )
		) );
	}

	public function ajax_reply() {
		$action = $_POST['bbp_reply_form_action'];
		if ( 'bbp-new-reply' == $action ) {
			bbp_new_reply_handler( $action );
		} elseif ( 'bbp-edit-reply' == $action ) {
			bbp_edit_reply_handler( $action );
		}
	}

	public function reply_post_extras( $reply_id ) {
		if ( ! bbp_is_ajax() ) {
			return;
		}

		ob_start();
		$reply_query = new WP_Query( array( 'p' => (int) $reply_id, 'post_type' => bbp_get_reply_post_type() ) );
		$bbp = bbpress();
		$bbp->reply_query = $reply_query;
		while ( bbp_replies() ) : bbp_the_reply();
			bbp_get_template_part( 'loop', 'single-reply' );
		endwhile;
		$reply_html = ob_get_clean();

		bbp_ajax_response( true, $reply_html );
	}

}

new bbPress_Ajax_Replies();