<?php
/**
 * Removes WordPress core assets and head tags this theme doesn't use.
 *
 * Each part is controlled by a switch in Appearance → Web Of D → Performance.
 *
 * @package Web_Of_D
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'wod_cleanup_wordpress' );

/**
 * Unhook the parts that can be removed site-wide.
 */
function wod_cleanup_wordpress() {
	if ( wod_option( 'disable_emoji' ) ) {
		wod_disable_emoji();
	}

	if ( wod_option( 'clean_head' ) ) {
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
		remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		// Only the discovery link/header: the REST API itself keeps working (the app needs it).
		remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
		remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	}
}

/**
 * Emoji detection script and styles, front end and admin. Browsers render emoji natively.
 */
function wod_disable_emoji() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'embed_head', 'print_emoji_detection_script' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'enqueue_embed_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );

	// wp-admin/includes/admin-filters.php adds the admin hooks after `init`, so unhook those later.
	add_action(
		'admin_init',
		static function () {
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'admin_enqueue_scripts', 'wp_enqueue_emoji_styles' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
		}
	);
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	// Classic editor's emoji plugin, and the s.w.org dns-prefetch hint.
	add_filter(
		'tiny_mce_plugins',
		static function ( $plugins ) {
			return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : $plugins;
		}
	);
	add_filter( 'emoji_svg_url', '__return_false' );
}

add_action( 'wp_enqueue_scripts', 'wod_remove_block_styles', 100 );

/**
 * Post content renders inside the React app with its own prose styles (src/index.css → prose-wod).
 * Every view is the app, and posts load without a page reload, so this applies site-wide.
 */
function wod_remove_block_styles() {
	if ( ! wod_option( 'block_styles' ) ) {
		return;
	}

	// wp-img-auto-sizes-contain only matters for WordPress-rendered images with sizes="auto".
	foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles', 'wp-img-auto-sizes-contain' ) as $handle ) {
		wp_dequeue_style( $handle );
	}
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
}
