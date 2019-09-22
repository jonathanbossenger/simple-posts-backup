<?php
/*
 * Plugin Name: Simple Posts Backup
 * Version: 1.0
 * Plugin URI: http://jonathanbossenger.com/
 * Description: Back up your post title and content.
 * Author: Jonathan Bossenger
 * Author URI: http://jonathanbossenger.com/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: simple-posts-backup
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Jonathan Bossenger
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Very, VERY simple logging function
 */
define( 'SBJ_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SBJ_LOG_PATH', trailingslashit( SBJ_PLUGIN_PATH ) . 'sbj.log.' . date( 'y-m-d' ) . '.log' );
define( 'SBJ_API_URL', 'http://192.168.10.10' );

function sbj_debug( $data ) {
	$file = SBJ_LOG_PATH;
	if ( ! is_file( $file ) ) {
		file_put_contents( $file, '' );
	}
	$data_string = print_r( $data, true ) . "\n";
	file_put_contents( $file, $data_string, FILE_APPEND );
}

// ------------------------------------------------------------------
// Add all your sections, fields and settings during admin_init
// ------------------------------------------------------------------
//
add_action( 'admin_init', 'sbj_settings_api_init' );

function sbj_settings_api_init() {

	sbj_debug( 'Settings API init' );

	// Add the section to reading settings so we can add our
	// fields to it
	add_settings_section(
		'sbj_settings_section',
		'Simple Posts Backup Settings',
		'sbj_settings_section_callback_function',
		'general'
	);

	// Add the field with the names and function to use for our new
	// settings, put it in our new section
	add_settings_field(
		'sbj_settings_email',
		'Email Address',
		'sbj_settings_email_callback_function',
		'general',
		'sbj_settings_section'
	);

	add_settings_field(
		'sbj_settings_api_token',
		'Api Token',
		'sbj_settings_api_token_callback_function',
		'general',
		'sbj_settings_section'
	);

	// Register our setting so that $_POST handling is done for us and
	// our callback function just has to echo the <input>
	register_setting( 'general', 'sbj_settings_email' );
	register_setting( 'general', 'sbj_settings_api_token' );
} // eg_settings_api_init()

// ------------------------------------------------------------------
// Settings section callback function
// ------------------------------------------------------------------
//
// This function is needed if we added a new section. This function
// will be run at the start of our section
//

function sbj_settings_section_callback_function() {
	echo '<p>Credentials for the Simple Posts Backup App</p>';
}

// ------------------------------------------------------------------
// Callback function for our example setting
// ------------------------------------------------------------------
//
// creates a checkbox true/false option. Other types are surely possible
//

function sbj_settings_email_callback_function() {
	echo '<input name="sbj_settings_email" id="sbj_settings_email" type="text" value="' . get_option( 'sbj_settings_email' ) . '" class="code"/> Enter your email address';
}

function sbj_settings_api_token_callback_function(){
	echo '<input name="sbj_settings_api_token" id="sbj_settings_api_token" type="text" value="' . get_option( 'sbj_settings_api_token' ) . '" class="code"/> Enter the api key';
}

if ( is_admin() ) {
	add_action( 'save_post', 'sbj_save_post', 10, 2 );
	add_action( 'post_updated', 'sbj_save_post', 10, 2 );
}

function sbj_save_post( $post_id, $post ) {

	sbj_debug( 'Save Post' );
	/**
	 * Only trigger this if the post is actually saved
	 */
	if ( isset( $post->post_status ) && ( 'auto-draft' === $post->post_status || 'inherit' === $post->post_status ) ) {
		return;
	}

	/**
	 * Don't trigger this when the post is trashed
	 */
	if ( 'trash' === $post->post_status ) {
		return;
	}

	sbj_debug( 'Ready to save post' );

	$api_url = SBJ_API_URL . '/api/posts';

	$api_token = get_option( 'sbj_settings_api_token', '' );

	sbj_debug( $api_token );

	$post_body = array(
		'api_token'    => $api_token,
		'post_id'      => $post->ID,
		'post_title'   => $post->post_title,
		'post_content' => $post->post_content,
	);

	sbj_debug( $post_body );

	$app_response = wp_remote_post(
		$api_url,
		array(
			'timeout' => 45,
			'body'    => $post_body,
		)
	);

	sbj_debug( $app_response );

	if ( is_wp_error( $app_response ) ) {
		sbj_debug( 'WordPress Error' );

		return;
	}

	$response_object = json_decode( wp_remote_retrieve_body( $app_response ) );

	if ( empty( $response_object ) ) {
		sbj_debug( 'Empty Response Object' );

		return;
	}

	if ( 'success' !== $response_object->status ) {
		if ( isset( $response_object->message ) ) {
			sbj_debug( 'Post wasn\'t added with message ' . $response_object->message );
		} else {
			sbj_debug( 'Post wasn\'t added with unknown reason' );
		}

		return;
	}

	sbj_debug( 'Post was successfully added' );
	sbj_debug( $response_object );

}
