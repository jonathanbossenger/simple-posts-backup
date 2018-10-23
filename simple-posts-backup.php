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

// ------------------------------------------------------------------
// Add all your sections, fields and settings during admin_init
// ------------------------------------------------------------------
//
add_action( 'admin_init', 'sbj_settings_api_init' );

function sbj_settings_api_init() {
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
