<?php
/**
 * Plugin Name: Read Progress Button
 * Description: A round button on posts that fills as you read and leads back to the homepage; it turns into a check mark at the end of the post. Choose its corner on Settings > Read Progress Button. Updates itself from the GitHub releases of stijn/read-progress-button.
 * Version: 1.2.0
 * Update URI: https://github.com/stijn/read-progress-button
 * Author: Stijn Vogels
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * License: Proprietary. Copyright (c) 2026 Stijn Vogels. All rights reserved.
 *
 * @package ReadProgressButton
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'READ_PROGRESS_BUTTON_VERSION', '1.2.0' );
define( 'READ_PROGRESS_BUTTON_FILE', __FILE__ );

require_once __DIR__ . '/includes/updater.php';
require_once __DIR__ . '/includes/settings.php';

/**
 * Where the button sits: 'top-left', 'top-right', 'bottom-left' or 'bottom-right'.
 */
function read_progress_button_position() : string {
	$position = (string) get_option( 'read_progress_button_position', 'top-left' );
	return array_key_exists( $position, read_progress_button_positions() ) ? $position : 'top-left';
}

function read_progress_button_positions() : array {
	return array(
		'top-left'     => 'Top left',
		'top-right'    => 'Top right',
		'bottom-left'  => 'Bottom left',
		'bottom-right' => 'Bottom right',
	);
}

// On single posts only: a small stylesheet and a deferred script, no jQuery.
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	wp_enqueue_style( 'read-progress-button', plugins_url( 'assets/read-progress-button.css', READ_PROGRESS_BUTTON_FILE ), array(), READ_PROGRESS_BUTTON_VERSION );
	wp_enqueue_script( 'read-progress-button', plugins_url( 'assets/read-progress-button.js', READ_PROGRESS_BUTTON_FILE ), array(), READ_PROGRESS_BUTTON_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
} );

// The button: a cross that leads to the homepage, inside a ring that fills as the post is read.
add_action( 'wp_footer', function () {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	printf(
		'<div class="rpb rpb--%1$s"><a class="rpb__link" href="%2$s" aria-label="%3$s">'
		. '<svg class="rpb__ring" viewBox="0 0 44 44" aria-hidden="true" focusable="false"><circle class="rpb__progress" cx="22" cy="22" r="20.5"/></svg>'
		. '<svg class="rpb__mark" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path class="rpb__icon" d="M12 10.588L6.702 5.29a1 1 0 0 0-1.408.004.993.993 0 0 0-.003 1.408L10.588 12l-5.297 5.297a1 1 0 0 0 .003 1.408.993.993 0 0 0 1.408.004L12 13.411l5.298 5.298a.999.999 0 0 0 1.411-1.412L13.412 12l5.297-5.298a1 1 0 0 0-.003-1.408.994.994 0 0 0-1.408-.004z"/></svg>'
		. '</a></div>' . "\n",
		esc_attr( read_progress_button_position() ),
		esc_url( home_url( '/' ) ),
		esc_attr( 0 === strpos( get_locale(), 'nl' ) ? 'Naar de homepage' : 'To the homepage' )
	);
} );
