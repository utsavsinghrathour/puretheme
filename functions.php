<?php
/**
 * Theme setup functions for PureTheme.
 *
 * @package puretheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'puretheme_setup' ) ) {
	/**
	 * Configure theme support for core block features.
	 */
	function puretheme_setup() {
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'responsive-embeds' );
		add_editor_style( 'style.css' );
	}
}
add_action( 'after_setup_theme', 'puretheme_setup' );
