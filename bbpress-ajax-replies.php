<?php
/*
 * Plugin Name: bbPress Ajax Replies
 * Plugin URI:  http://wordpress.org/plugins/bbpress-ajax-replies/
 * Description: Gives topic replies in bbPress more ajaxy goodness.
 * Author:      Alison Barrett
 * Author URI:  http://alisothegeek.com/
 * Version:     1.0
 */

class bbPress_Ajax_Replies {

	/**
	 * @var string
	 */
	private $url;

	/**
	 * Constructor
	 */
	function __construct() {
		$this->setup_globals();
		$this->setup_actions();
	}

	private function setup_globals() {
		$this->url = trailingslashit( plugins_url( basename( __DIR__ ) ) );
	}

	/**
	 * Set up bbpress hooks
	 *
	 * Using bbPress sub-actions to avoid plugin loading without bbPress
	 * being activated
	 */
	private function setup_actions() {
		add_action( 'bbp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'bbp_enqueue_scripts', array( $this, 'localize_reply_ajax_script' ) );
		add_action( 'bbp_ajax_reply', array( $this, 'ajax_reply' ) );
		add_action( 'bbp_new_reply_post_extras', array( $this, 'new_reply_post_extras' ), 99 );
		add_action( 'bbp_edit_reply_post_extras', array( $this, 'edit_reply_post_extras' ), 99 );
	}

	public function enqueue_scripts() {
		if ( bbp_is_single_topic() ) {
			wp_enqueue_script( 'bbpress-reply-ajax', $this->url . 'reply-ajax.js', array( 'bbpress-topic', 'bbpress-reply', 'jquery', 'jquery-color' ) );
		}
	}

	public function localize_reply_ajax_script() {
		// Bail if not viewing a single topic
		if ( ! bbp_is_single_topic() )
			return;

		wp_localize_script( 'bbpress-reply-ajax', 'bbpReplyAjaxJS', array(
			'bbp_ajaxurl'        => bbp_get_ajax_url(),
			'generic_ajax_error' => __( 'Something went wrong. Refresh your browser and try again.', 'bbpress' ),
			'is_user_logged_in'  => is_user_logged_in(),
			'reply_nonce'        => wp_create_nonce( 'reply-ajax_' . get_the_ID() )
		) );
	}

	/**
	 * Ajax handler for reply submissions
	 *
	 * This is attached to the appropriate bbPress ajax hooks, so it is fired
	 * on any bbPress ajax submissions with the 'action' parameter set to
	 * 'reply'
	 */
	public function ajax_reply() {
		$action = $_POST['bbp_reply_form_action'];
		if ( 'bbp-new-reply' == $action ) {
			bbp_new_reply_handler( $action );
		} elseif ( 'bbp-edit-reply' == $action ) {
			bbp_edit_reply_handler( $action );
		}
	}

	/**
	 * New replies
	 *
	 * @param $reply_id
	 */
	public function new_reply_post_extras( $reply_id ) {
		if ( ! bbp_is_ajax() ) {
			return;
		}

		$this->ajax_response( $reply_id, 'new' );
	}

	/**
	 * Editing an existing reply
	 *
	 * @param $reply_id
	 */
	public function edit_reply_post_extras( $reply_id ) {
		if ( ! bbp_is_ajax() ) {
			return;
		}

		$this->ajax_response( $reply_id, 'edit' );
	}

	/**
	 * Generate an ajax response
	 *
	 * Sends the HTML for the reply along with some extra information
	 *
	 * @param $reply_id
	 * @param $type
	 */
	private function ajax_response( $reply_id, $type ) {

		$reply_html = $this->get_reply_html( $reply_id );
		$extra_info = array(
			'reply_id'     => $reply_id,
			'reply_type'   => $type,
			'reply_parent' => (int) $_REQUEST['bbp_reply_to'],
		);
		bbp_ajax_response( true, $reply_html, null, $extra_info );
	}

	/**
	 * Uses a bbPress template file to generate reply HTML
	 *
	 * @param $reply_id
	 * @return string
	 */
	private function get_reply_html( $reply_id ) {
		ob_start();
		$reply_query = new WP_Query( array( 'p' => (int) $reply_id, 'post_type' => bbp_get_reply_post_type() ) );
		$bbp              = bbpress();
		$bbp->reply_query = $reply_query;
		while ( bbp_replies() ) : bbp_the_reply();
			bbp_get_template_part( 'loop', 'single-reply' );
		endwhile;
		$reply_html = ob_get_clean();

		return $reply_html;
	}

}

new bbPress_Ajax_Replies();