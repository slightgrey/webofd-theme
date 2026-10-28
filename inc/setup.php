<?php
/**
 * Theme supports and image sizes.
 *
 * @package Web_Of_D
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'wod_setup' );

/**
 * Register theme features.
 */
function wod_setup() {
	load_theme_textdomain( 'web-of-d', WOD_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	// Mission-row thumbnail; shown at 168x105, so this is 2x for retina.
	add_image_size( 'wod-card', 336, 210, true );

	register_nav_menus(
		array(
			'primary' => __( 'Header links (after Trophies)', 'web-of-d' ),
		)
	);
}
