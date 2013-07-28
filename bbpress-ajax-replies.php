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

	}

}

new bbPress_Ajax_Replies();