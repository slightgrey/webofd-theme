<?php
/**
 * Builds the JSON the React app reads as `window.wodData`.
 *
 * @package Web_Of_D
 */

defined( 'ABSPATH' ) || exit;

/**
 * Decode entities and strip tags. React escapes on output, and WP's title/excerpt output is already entity-encoded.
 *
 * @param string $html Raw HTML.
 * @return string
 */
function wod_plain_text( $html ) {
	return html_entity_decode( wp_strip_all_tags( (string) $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
}

/**
 * Category IDs of a post, primary first. Honours the Yoast / Rank Math primary category when one is set.
 *
 * @param int $post_id Post ID.
 * @return int[]
 */
function wod_post_category_ids( $post_id ) {
	$ids = array_map( 'intval', wp_get_post_categories( $post_id ) );

	foreach ( array( '_yoast_wpseo_primary_category', 'rank_math_primary_category' ) as $key ) {
		$primary = (int) get_post_meta( $post_id, $key, true );
		if ( $primary && in_array( $primary, $ids, true ) ) {
			return array_values( array_unique( array_merge( array( $primary ), $ids ) ) );
		}
	}

	return $ids;
}

/**
 * The fields of one post the board, search, XP and trophies need.
 *
 * @param WP_Post $post Post.
 * @return array
 */
function wod_post_summary( $post ) {
	$thumb = get_post_thumbnail_id( $post );
	$image = $thumb ? wp_get_attachment_image_src( $thumb, 'wod-card' ) : false;

	return array(
		'id'          => $post->ID,
		'slug'        => $post->post_name,
		'url'         => get_permalink( $post ),
		'title'       => wod_plain_text( get_the_title( $post ) ),
		'excerpt'     => wod_plain_text( get_the_excerpt( $post ) ),
		'date'        => get_the_date( 'M j, Y', $post ),
		'categories'  => wod_post_category_ids( $post->ID ),
		'readMinutes' => wod_read_minutes( $post ),
		'image'       => $image ? $image[0] : null,
		'quiz'        => wod_get_quiz( $post->ID ),
		'author'      => (int) $post->post_author,
	);
}

/**
 * Summary of every published post: enough for the board, search, XP and trophies on any page.
 * Cached until a post, term or featured image changes.
 *
 * @return array[]
 */
function wod_get_post_summaries() {
	$cached = get_transient( 'wod_posts' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$posts = get_posts(
		array(
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'posts_per_page'   => -1,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'suppress_filters' => false,
		)
	);

	$summaries = array_map( 'wod_post_summary', $posts );

	set_transient( 'wod_posts', $summaries, DAY_IN_SECONDS );
	return $summaries;
}

/**
 * Drop the summary cache.
 */
function wod_flush_post_summaries() {
	delete_transient( 'wod_posts' );
}
foreach ( array( 'save_post', 'wp_after_insert_post', 'deleted_post', 'edited_category', 'delete_category', 'switch_theme' ) as $wod_hook ) {
	add_action( $wod_hook, 'wod_flush_post_summaries' );
}
foreach ( array( 'added_post_meta', 'updated_post_meta', 'deleted_post_meta' ) as $wod_hook ) {
	add_action( $wod_hook, 'wod_flush_on_meta_change', 10, 3 );
}

/**
 * Featured image and quiz changes don't always trigger save_post (e.g. REST meta updates).
 *
 * @param int|int[] $meta_id  Meta ID(s).
 * @param int       $post_id  Post ID.
 * @param string    $meta_key Meta key.
 */
function wod_flush_on_meta_change( $meta_id, $post_id, $meta_key ) {
	if ( in_array( $meta_key, array( '_thumbnail_id', 'wod_quiz' ), true ) ) {
		wod_flush_post_summaries();
	}
}

/**
 * Categories with posts, most used first (the skill tree).
 *
 * @return array[]
 */
function wod_get_categories() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'orderby'    => 'count',
			'order'      => 'DESC',
			'hide_empty' => true,
		)
	);
	if ( is_wp_error( $terms ) ) {
		return array();
	}

	return array_map(
		static function ( $term ) {
			return array(
				'id'    => $term->term_id,
				'name'  => wod_plain_text( $term->name ),
				'slug'  => $term->slug,
				'count' => (int) $term->count,
				'url'   => get_term_link( $term ),
			);
		},
		$terms
	);
}

/**
 * Give every H2 an id and return the table of contents.
 *
 * @param string $html Rendered post content.
 * @return array{0: string, 1: array[]} Updated HTML and [{id, text}] list.
 */
function wod_add_heading_ids( $html ) {
	$toc  = array();
	$used = array();

	$html = preg_replace_callback(
		'#<h2(\s[^>]*)?>(.*?)</h2>#is',
		static function ( $m ) use ( &$toc, &$used ) {
			$attrs = $m[1] ?? '';
			$text  = trim( wod_plain_text( $m[2] ) );

			if ( preg_match( '/\sid=["\']([^"\']+)["\']/i', $attrs, $id_match ) ) {
				$id = $id_match[1];
			} else {
				$base = sanitize_title( $text );
				$base = '' !== $base ? $base : 'section';
				$id   = $base;
				for ( $n = 2; isset( $used[ $id ] ); $n++ ) {
					$id = $base . '-' . $n;
				}
				$attrs .= ' id="' . esc_attr( $id ) . '"';
			}

			$used[ $id ] = true;
			$toc[]       = array(
				'id'   => $id,
				'text' => $text,
			);

			return '<h2' . $attrs . '>' . $m[2] . '</h2>';
		},
		$html
	);

	return array( $html, $toc );
}

/**
 * Approved comments on a post, oldest first.
 *
 * @param WP_Post $post Post.
 * @return array[]
 */
function wod_get_comments( $post ) {
	$comments = get_comments(
		array(
			'post_id' => $post->ID,
			'status'  => 'approve',
			'type'    => 'comment',
			'order'   => 'ASC',
		)
	);

	return array_map(
		static function ( $comment ) use ( $post ) {
			return array(
				'id'       => (int) $comment->comment_ID,
				'name'     => wod_plain_text( $comment->comment_author ),
				'date'     => get_comment_date( 'M j, Y', $comment ),
				'text'     => wod_plain_text( $comment->comment_content ),
				'isAuthor' => $comment->user_id && (int) $comment->user_id === (int) $post->post_author,
			);
		},
		$comments
	);
}

/**
 * The full current post (single view only).
 *
 * @param WP_Post $post Post.
 * @return array
 */
function wod_get_post_detail( $post ) {
	// the_content filters (blocks, shortcodes) expect the post to be set up as the current one.
	$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	setup_postdata( $post );
	$content = apply_filters( 'the_content', get_the_content( null, false, $post ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core hook.

	list( $content, $toc ) = wod_add_heading_ids( str_replace( ']]>', ']]&gt;', $content ) );

	return array_merge(
		wod_post_summary( $post ),
		array(
			'type'         => $post->post_type,
			'featured'     => wod_featured_image( $post ),
			'contentHtml'  => $content,
			'toc'          => $toc,
			'comments'     => 'post' === $post->post_type ? wod_get_comments( $post ) : array(),
			'commentsOpen' => comments_open( $post ),
		)
	);
}

/**
 * Full-width featured image for the top of a post or page, with a srcset so the browser
 * picks the right size. The board uses the small `wod-card` crop instead.
 *
 * @param WP_Post $post Post.
 * @return array|null
 */
function wod_featured_image( $post ) {
	$id  = get_post_thumbnail_id( $post );
	$src = $id ? wp_get_attachment_image_src( $id, 'large' ) : false;
	if ( ! $src ) {
		return null;
	}

	return array(
		'src'    => $src[0],
		'width'  => (int) $src[1],
		'height' => (int) $src[2],
		'srcset' => (string) wp_get_attachment_image_srcset( $id, 'large' ),
		'alt'    => wod_plain_text( get_post_meta( $id, '_wp_attachment_image_alt', true ) ),
	);
}

/**
 * Which screen React should render for the current request.
 *
 * @return string home | category | post | page | 404
 */
function wod_current_view() {
	if ( is_singular( 'post' ) ) {
		return 'post';
	}
	if ( is_singular() ) {
		return 'page';
	}
	if ( is_category() ) {
		return 'category';
	}
	if ( is_404() ) {
		return '404';
	}
	return 'home';
}

/**
 * Branding, homepage copy and links from Appearance → Web Of D. Filterable with `wod_site`.
 *
 * @return array
 */
function wod_site_data() {
	$blogname = wod_plain_text( get_bloginfo( 'name' ) );
	$tagline  = wod_plain_text( get_bloginfo( 'description' ) );

	return apply_filters(
		'wod_site',
		array(
			'name'      => wod_option( 'brand_name' ) ? wod_option( 'brand_name' ) : $blogname,
			'mark'      => wod_option( 'brand_mark' ),
			'homeUrl'   => home_url( '/' ),
			'hireLabel' => wod_option( 'hire_label' ),
			'hireUrl'   => wod_option( 'hire_url' ),
			'githubUrl' => wod_option( 'github_url' ),
			'feedUrl'   => get_feed_link(),
			'menu'      => wod_menu_items( 'primary' ),
			'owner'     => wod_option( 'owner' ),
			'eyebrow'   => wod_option( 'hero_eyebrow' ),
			'heading'   => wod_option( 'hero_heading' ),
			'intro'     => wod_option( 'hero_intro' ),
			// Document titles for views the app switches to without a reload.
			'title'     => $blogname,
			'homeTitle' => $tagline ? $blogname . ' ' . wod_title_separator() . ' ' . $tagline : $blogname,
			'titleSep'  => wod_title_separator(),
		)
	);
}

/**
 * Top-level items of the menu assigned to a location (Appearance → Menus).
 *
 * @param string $location Menu location.
 * @return array[]
 */
function wod_menu_items( $location ) {
	$locations = get_nav_menu_locations();
	$items     = empty( $locations[ $location ] ) ? array() : wp_get_nav_menu_items( $locations[ $location ] );

	$top = array_filter(
		(array) $items,
		static function ( $item ) {
			return 0 === (int) $item->menu_item_parent;
		}
	);

	return array_values(
		array_map(
			static function ( $item ) {
				return array(
					'id'     => (int) $item->ID,
					'title'  => wod_plain_text( $item->title ),
					'url'    => $item->url,
					'target' => $item->target,
				);
			},
			$top
		)
	);
}

/**
 * The separator WordPress uses in document titles.
 *
 * @return string
 */
function wod_title_separator() {
	return wod_plain_text( apply_filters( 'document_title_separator', '-' ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core hook.
}

/**
 * Game rules from Appearance → Web Of D, read by src/lib/config.js.
 *
 * @return array
 */
function wod_game_config() {
	return array(
		'xpPerLevel'       => (int) wod_option( 'xp_per_level' ),
		'skillsShown'      => (int) wod_option( 'skills_shown' ),
		'autoComplete'     => (bool) wod_option( 'auto_complete' ),
		'showQuiz'         => (bool) wod_option( 'show_quiz' ),
		'showToc'          => (bool) wod_option( 'show_toc' ),
		'shopCategorySlug' => (string) wod_option( 'shop_category' ),
		'spa'              => (bool) wod_option( 'spa' ),
		'recaptcha'        => wod_recaptcha_client_config(),
	);
}

/**
 * Everything the React app needs for this request.
 *
 * @return array
 */
function wod_get_app_data() {
	$view = wod_current_view();

	$data = array(
		'view'       => $view,
		'categoryId' => 'category' === $view ? get_queried_object_id() : null,
		'search'     => is_search() ? get_search_query( false ) : '',
		'posts'      => wod_get_post_summaries(),
		'categories' => wod_get_categories(),
		'post'       => null,
		'site'       => wod_site_data(),
		'config'     => wod_game_config(),
		'rest'       => array(
			'root'  => esc_url_raw( rest_url() ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		),
	);

	if ( 'post' === $view || 'page' === $view ) {
		$data['post'] = wod_get_post_detail( get_queried_object() );
	}

	return $data;
}
