<?php
/**
 * REST API additions: read time, and guest comments.
 *
 * @package Web_Of_D
 */

defined( 'ABSPATH' ) || exit;

/**
 * Estimated read time in minutes at 200 words per minute.
 *
 * @param WP_Post|int $post Post.
 * @return int
 */
function wod_read_minutes( $post ) {
	$post  = get_post( $post );
	$words = $post ? str_word_count( wp_strip_all_tags( $post->post_content ) ) : 0;
	return max( 1, (int) ceil( $words / 200 ) );
}

add_action( 'rest_api_init', 'wod_register_rest_fields' );

/**
 * Add `read_minutes` to post responses.
 */
function wod_register_rest_fields() {
	register_rest_field(
		'post',
		'read_minutes',
		array(
			'get_callback' => static function ( $post ) {
				return wod_read_minutes( $post['id'] );
			},
			'schema'       => array(
				'description' => __( 'Estimated read time in minutes.', 'web-of-d' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
		)
	);
}

// Readers comment as guests through POST /wp/v2/comments.
add_filter( 'rest_allow_anonymous_comments', '__return_true' );

add_action( 'rest_api_init', 'wod_register_view_route' );

/**
 * GET /wod/v1/view?url=… returns what the app needs to show a post or page without a
 * full reload: the same `post` payload the page itself prints into window.wodData.
 */
function wod_register_view_route() {
	register_rest_route(
		'wod/v1',
		'/view',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'wod_rest_view',
			'permission_callback' => '__return_true',
			'args'                => array(
				'url' => array(
					'type'     => 'string',
					'format'   => 'uri',
					'required' => true,
				),
			),
		)
	);
}

/**
 * Resolve a front-end URL to a published post or page.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function wod_rest_view( $request ) {
	$post = get_post( url_to_postid( $request['url'] ) );

	// Only content anyone could open in the browser; everything else falls back to a normal page load.
	if ( ! $post || 'publish' !== $post->post_status || ! is_post_publicly_viewable( $post ) || post_password_required( $post ) ) {
		return new WP_Error( 'wod_not_found', __( 'Not found.', 'web-of-d' ), array( 'status' => 404 ) );
	}

	return rest_ensure_response(
		array(
			'view'  => 'post' === $post->post_type ? 'post' : 'page',
			'title' => wod_plain_text( get_the_title( $post ) ) . ' ' . wod_title_separator() . ' ' . wod_plain_text( get_bloginfo( 'name' ) ),
			'post'  => wod_get_post_detail( $post ),
		)
	);
}
