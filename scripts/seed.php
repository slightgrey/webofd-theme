<?php
/**
 * Dev-only demo content: the prototype's 8 missions with categories, quizzes,
 * comments and generated featured images. Not included in the theme zip.
 *
 * Run inside wp-env:  npm run seed
 * (= wp-env run cli wp eval-file wp-content/wod-seed/seed.php)
 *
 * Or against a local install without WP-CLI (e.g. Laragon), from the theme folder:
 *   php scripts/seed.php https://vincent.test
 *
 * Safe to re-run: posts whose slug already exists are skipped.
 *
 * @package Web_Of_D
 */

// Standalone run: bootstrap WordPress from the install this theme lives in.
if ( ! defined( 'ABSPATH' ) ) {
	if ( 'cli' !== PHP_SAPI ) {
		exit;
	}
	$wod_url                    = wod_seed_parse_url( $argv[1] ?? 'http://localhost' );
	$_SERVER['REQUEST_SCHEME']  = $wod_url['scheme'];
	$_SERVER['HTTPS']           = 'https' === $wod_url['scheme'] ? 'on' : 'off';
	$_SERVER['HTTP_HOST']       = $wod_url['host'];
	$_SERVER['SERVER_NAME']     = $wod_url['host'];
	$_SERVER['REQUEST_URI']     = '/';
	$_SERVER['REQUEST_METHOD']  = 'GET';
	require dirname( __DIR__, 4 ) . '/wp-load.php';
}

/**
 * Scheme and host of a URL (wp_parse_url isn't loaded yet).
 *
 * @param string $url URL.
 * @return array{scheme: string, host: string}
 */
function wod_seed_parse_url( $url ) {
	$parts = parse_url( $url ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
	return array(
		'scheme' => $parts['scheme'] ?? 'http',
		'host'   => ( $parts['host'] ?? 'localhost' ) . ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' ),
	);
}

// Minimal stand-in for the WP_CLI logger when running standalone.
if ( ! class_exists( 'WP_CLI' ) ) {
	class WP_CLI { // phpcs:ignore Generic.Files.OneObjectStructurePerFile.MultipleFound
		public static function log( $msg ) {
			echo $msg . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		public static function success( $msg ) {
			self::log( 'Success: ' . $msg );
		}
		public static function warning( $msg ) {
			self::log( 'Warning: ' . $msg );
		}
		public static function error( $msg ) {
			self::log( 'Error: ' . $msg );
			exit( 1 );
		}
	}
	define( 'WOD_SEED_STANDALONE', true );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

// The theme must already be active when WordPress loads, or the wod-card image size and the
// wod_quiz meta aren't registered (npm run seed activates it in a separate command first).
if ( 'web-of-d' !== get_stylesheet() ) {
	WP_CLI::error( 'Activate the theme first: wp theme activate web-of-d' );
}

wp_set_current_user( 1 );

// Site-wide settings are only touched in the throwaway wp-env install, never a real local site.
if ( ! defined( 'WOD_SEED_STANDALONE' ) ) {
	update_option( 'blogname', 'Web Of D' );
	update_option( 'permalink_structure', '/%postname%/' );
	flush_rewrite_rules();
	wp_update_user(
		array(
			'ID'           => 1,
			'display_name' => 'Vincent Layte',
		)
	);
}

// The default "Hello world!" post would show up as an Uncategorized mission (trashed, so it can be restored).
$wod_hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
if ( $wod_hello ) {
	wp_trash_post( $wod_hello->ID );
}

$wod_posts = array(
	array(
		'slug'    => 'headless-wp-react',
		'cats'    => array( 'Headless', 'React', 'REST API' ),
		'date'    => '2026-10-14 09:00:00',
		'read'    => 9,
		'title'   => 'Building a headless WordPress front end with React and the REST API',
		'excerpt' => 'WordPress as the CMS, React as a client-side SPA. Fetching posts, routing without reloads and keeping previews working for editors.',
		'quiz'    => array( 'Which query parameter trims a REST API response to only the fields you need?', array( '_embed', '_fields', 'context=view' ), 1 ),
		'sections' => array(
			array( 'Why go headless', array( 'Editors keep the admin they know. The front end gets component-driven UI, instant route changes and a deploy pipeline that does not touch PHP.', 'The trade-off is that you own things WordPress used to do for you: previews, SEO tags and menu rendering.' ) ),
			array( 'Fetching posts', array( 'The REST API exposes posts at /wp-json/wp/v2/posts. Request only the fields you render with _fields to keep payloads small.' ), 'js', "const res = await fetch(\n  `\${WP}/wp-json/wp/v2/posts?_fields=id,slug,title,excerpt&per_page=10`\n);\nconst posts = await res.json();" ),
			array( 'Client-side routing', array( 'Map WordPress slugs to routes and fetch on navigation. Cache responses per slug so the back button is instant.' ) ),
			array( 'Keeping previews working', array( 'Point the preview link at a front-end route that reads the nonce and fetches the revision with the authenticated endpoint.' ) ),
		),
		'comments' => array(
			array( 'Marta Ilieva', '2026-10-15 10:12:00', 'How are you handling Yoast meta on the React side? Pulling yoast_head_json?' ),
			array( 'Vincent Layte', '2026-10-15 14:40:00', '@Marta yes, yoast_head_json per route, then a small Head component maps it to tags.', true ),
			array( 'Dev Okafor', '2026-10-17 08:05:00', 'The preview section saved me a day. Thanks.' ),
		),
	),
	array(
		'slug'    => 'woo-custom-checkout',
		'cats'    => array( 'WooCommerce', 'PHP', 'Gutenberg' ),
		'date'    => '2026-09-30 09:00:00',
		'read'    => 7,
		'title'   => 'Adding custom checkout fields in WooCommerce blocks',
		'excerpt' => 'The block checkout changed the rules. How to register, validate and save extra fields without overriding templates.',
		'quiz'    => array( 'Which hook do you register additional checkout fields on?', array( 'woocommerce_checkout_fields', 'init', 'woocommerce_init' ), 2 ),
		'sections' => array(
			array( 'The block checkout is different', array( 'Classic hooks like woocommerce_checkout_fields do not touch the block checkout. Fields are now registered through the Additional Checkout Fields API.' ) ),
			array( 'Registering a field', array( 'Register on woocommerce_init so the field is available to the Store API.' ), 'php', "add_action( 'woocommerce_init', function () {\n  woocommerce_register_additional_checkout_field( [\n    'id'       => 'vl/gift-note',\n    'label'    => 'Gift note',\n    'location' => 'order',\n    'type'     => 'text',\n  ] );\n} );" ),
			array( 'Validation and saving', array( 'Validation runs server-side through the woocommerce_validate_additional_field action. Values save to order meta automatically.' ) ),
		),
		'comments' => array(
			array( 'Lena Brandt', '2026-10-02 09:30:00', 'Does this work with the express payment buttons too?' ),
			array( 'Vincent Layte', '2026-10-02 12:00:00', '@Lena order-location fields are skipped by express checkout, so make them optional.', true ),
		),
	),
	array(
		'slug'    => 'wp-env-playwright',
		'cats'    => array( 'Tooling', 'Testing', 'DevOps' ),
		'date'    => '2026-09-12 09:00:00',
		'read'    => 6,
		'title'   => 'A local plugin test setup with wp-env and Playwright',
		'excerpt' => 'Spin up a disposable WordPress, seed it, and run end-to-end tests on every push.',
		'quiz'    => array( 'What file configures a wp-env environment?', array( '.wp-env.json', 'wp-config.php', 'docker-compose.yml' ), 0 ),
		'sections' => array(
			array( 'Starting wp-env', array( 'wp-env runs WordPress in Docker from a single .wp-env.json. Map your plugin folder and it is active on boot.' ), 'json', "{\n  \"core\": \"WordPress/WordPress\",\n  \"plugins\": [ \".\" ],\n  \"port\": 8888\n}" ),
			array( 'Writing the first test', array( 'The @wordpress/e2e-test-utils-playwright package handles login and admin navigation, so tests stay focused on behaviour.' ) ),
			array( 'Running in CI', array( 'Start wp-env in the workflow, wait for the port, then run Playwright. Cache the Docker layers to keep runs under three minutes.' ) ),
		),
	),
	array(
		'slug'    => 'custom-blocks-2026',
		'cats'    => array( 'WordPress', 'Gutenberg', 'React' ),
		'date'    => '2026-08-27 09:00:00',
		'read'    => 8,
		'title'   => 'Custom Gutenberg blocks with block.json and wp-scripts',
		'excerpt' => 'Scaffold, build and ship a dynamic block. Attributes, server rendering and keeping the editor preview honest.',
		'quiz'    => array( 'Which file makes a block render on the server?', array( 'index.js', 'render.php', 'style.scss' ), 1 ),
		'sections' => array(
			array( 'Scaffolding', array( 'npx @wordpress/create-block gives you block.json, an edit component and a build script in under a minute.' ), 'bash', 'npx @wordpress/create-block@latest vl-callout --variant dynamic' ),
			array( 'Dynamic rendering', array( 'A render.php file referenced from block.json lets the front end stay in sync with data that changes after the post is saved.' ) ),
			array( 'Editor parity', array( 'Use ServerSideRender sparingly. For most blocks, a faithful edit component reads better and avoids a request on every keystroke.' ) ),
		),
	),
	array(
		'slug'    => 'react-query-wp',
		'cats'    => array( 'React', 'Headless', 'Performance' ),
		'date'    => '2026-08-09 09:00:00',
		'read'    => 5,
		'title'   => 'Caching WordPress data in React with TanStack Query',
		'excerpt' => 'Stop refetching the same posts. Query keys, stale times and prefetching on hover.',
		'quiz'    => array( 'When is a good moment to prefetch a post?', array( 'On pointerenter', 'On window load', 'After the click' ), 0 ),
		'sections' => array(
			array( 'Query keys that match routes', array( 'Key queries by resource and slug so cache entries line up with pages.' ), 'js', "useQuery({\n  queryKey: ['post', slug],\n  queryFn: () => getPost(slug),\n  staleTime: 5 * 60 * 1000,\n});" ),
			array( 'Prefetch on hover', array( 'Call queryClient.prefetchQuery on pointerenter. By the time the click lands, the data is already there.' ) ),
		),
	),
	array(
		'slug'    => 'wp-security-checklist',
		'cats'    => array( 'WordPress', 'Security', 'PHP' ),
		'date'    => '2026-07-22 09:00:00',
		'read'    => 10,
		'title'   => 'A security checklist for every plugin I ship',
		'excerpt' => 'Nonces, capability checks, sanitising input and escaping output. The review I run before tagging a release.',
		'quiz'    => array( 'A nonce proves intent. What proves permission?', array( 'wp_verify_nonce', 'current_user_can', 'is_admin' ), 1 ),
		'sections' => array(
			array( 'Check capability first', array( 'Every admin action and REST route starts with current_user_can. A nonce proves intent; it does not prove permission.' ) ),
			array( 'Sanitise in, escape out', array( 'Sanitise when data enters, escape when it leaves. Late escaping catches the cases you forgot.' ), 'php', "\$title = sanitize_text_field( wp_unslash( \$_POST['title'] ?? '' ) );\necho '<h2>' . esc_html( \$title ) . '</h2>';" ),
			array( 'Prepared queries', array( 'Any query that touches user input goes through $wpdb->prepare. No exceptions for "trusted" admin input.' ) ),
		),
		'comments' => array(
			array( 'Sam Rivera', '2026-07-24 16:20:00', 'Printing this and taping it next to my monitor.' ),
		),
	),
	array(
		'slug'    => 'tailwind-wp-theme',
		'cats'    => array( 'Tooling', 'Performance', 'WordPress' ),
		'date'    => '2026-07-03 09:00:00',
		'read'    => 6,
		'title'   => 'Tailwind and Vite in a classic WordPress theme',
		'excerpt' => 'Hot reload in PHP templates, a production manifest, and enqueueing hashed assets correctly.',
		'quiz'    => array( 'In production, where do hashed asset names come from?', array( 'functions.php', 'manifest.json', 'style.css header' ), 1 ),
		'sections' => array(
			array( 'Dev server', array( 'Run Vite alongside your local WordPress and enqueue the dev client when a hot file exists.' ) ),
			array( 'Reading the manifest', array( 'In production, read manifest.json and enqueue the hashed file names so caches bust on every build.' ) ),
		),
	),
	array(
		'slug'    => 'woo-rest-orders',
		'cats'    => array( 'WooCommerce', 'REST API', 'PHP' ),
		'date'    => '2026-06-18 09:00:00',
		'read'    => 7,
		'title'   => 'Syncing WooCommerce orders to an external system',
		'excerpt' => 'Webhooks versus polling, idempotency keys, and what to do when the other side is down.',
		'quiz'    => array( 'How do you skip a duplicate webhook retry?', array( 'Compare order ID and modified date', 'Disable retries', 'Poll instead' ), 0 ),
		'sections' => array(
			array( 'Webhooks first', array( 'WooCommerce webhooks fire on order.created and order.updated. Verify the signature header before trusting the payload.' ) ),
			array( 'Retries and idempotency', array( 'Store the order ID and modified date you last sent. If a retry arrives with the same pair, skip it.' ) ),
		),
	),
);

// Filler so each post's word count gives the prototype's read time (read_minutes = ceil(words / 200)).
$wod_filler = array(
	'Start with the smallest version that works end to end, then widen it. A thin slice that runs in production teaches you more than a complete design that only exists in a branch.',
	'Write down the assumptions you are making before you build on them. When something breaks later, that list is usually where the answer is hiding.',
	'Keep the moving parts visible. A short README section on how the pieces connect saves the next person, often future you, an afternoon of reading code.',
	'Measure before you optimise. Query Monitor, the browser network panel and a profiler will each tell you something the others miss, and guessing rarely beats any of them.',
	'Treat configuration as code. Anything you click in an admin screen to make the setup work belongs in a script, a constant or a version-controlled file.',
	'Plan for the failure path as carefully as the happy path. Timeouts, empty responses and partial data are normal, so decide up front what the user sees when they happen.',
	'Name things after what they do for the reader, not how they are implemented. Good names make reviews faster and turn comments into a last resort.',
	'Automate the checks you keep forgetting. A lint rule or a CI step costs minutes to add and quietly removes a whole class of review comments.',
	'Ship behind a flag when the change is risky. Turning a feature off is a lot calmer than reverting a deploy while support tickets arrive.',
	'Leave the codebase a little clearer than you found it. Small, steady refactors keep a project pleasant to work on without ever needing a rewrite.',
);

/**
 * Category ID by name, created on first use.
 *
 * @param string $name Category name.
 * @return int
 */
function wod_seed_category( $name ) {
	$slug = 'WooCommerce' === $name ? 'woocommerce' : sanitize_title( $name );
	$term = get_term_by( 'slug', $slug, 'category' );
	if ( $term ) {
		return (int) $term->term_id;
	}
	$created = wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
	return is_wp_error( $created ) ? 0 : (int) $created['term_id'];
}

/**
 * Block markup for a post's sections, padded with filler to the target read time.
 *
 * @param array    $post   Seed entry.
 * @param string[] $filler Filler paragraphs.
 * @return string
 */
function wod_seed_content( $post, $filler ) {
	$sections = $post['sections'];
	$words    = 0;
	foreach ( $sections as $section ) {
		$words += str_word_count( $section[0] . ' ' . implode( ' ', $section[1] ) . ' ' . ( $section[3] ?? '' ) );
	}

	// Aim just under read * 200 words so ceil() lands on the intended minutes.
	$target = $post['read'] * 200 - 60;
	for ( $i = 0; $words < $target; $i++ ) {
		$para                                      = $filler[ $i % count( $filler ) ];
		$sections[ $i % count( $sections ) ][1][] = $para;
		$words                                    += str_word_count( $para );
	}

	$html = '';
	foreach ( $sections as $section ) {
		$html .= "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . esc_html( $section[0] ) . "</h2>\n<!-- /wp:heading -->\n\n";
		$paras = $section[1];
		// Code goes after the first paragraph, as in the prototype.
		$first = array_shift( $paras );
		$html .= "<!-- wp:paragraph -->\n<p>" . esc_html( $first ) . "</p>\n<!-- /wp:paragraph -->\n\n";
		if ( ! empty( $section[3] ) ) {
			$lang  = $section[2];
			$html .= '<!-- wp:code {"className":"language-' . $lang . '"} -->' . "\n" . '<pre class="wp-block-code language-' . $lang . '"><code>' . esc_html( $section[3] ) . "</code></pre>\n<!-- /wp:code -->\n\n";
		}
		foreach ( $paras as $para ) {
			$html .= "<!-- wp:paragraph -->\n<p>" . esc_html( $para ) . "</p>\n<!-- /wp:paragraph -->\n\n";
		}
	}
	return $html;
}

/**
 * Generate a gradient PNG and attach it as the post's featured image.
 *
 * @param int $post_id Post ID.
 * @param int $index   Post index (varies the colours).
 */
function wod_seed_image( $post_id, $index ) {
	if ( ! function_exists( 'imagecreatetruecolor' ) ) {
		WP_CLI::warning( 'GD is not available; skipping featured images.' );
		return;
	}

	$w   = 1344;
	$h   = 840;
	$img = imagecreatetruecolor( $w, $h );
	// Nocturne-ish palette: deep indigo into the accent, rotated a little per post.
	$from = array( 0x16 + $index * 3, 0x18 + $index * 2, 0x26 + $index * 4 );
	$to   = array( 0x79 - $index * 6, 0x6c + $index * 5, 0xbf - $index * 4 );
	for ( $x = 0; $x < $w; $x++ ) {
		for ( $y = 0; $y < $h; $y += 4 ) {
			$t     = min( 1, ( $x / $w ) * 0.7 + ( 1 - $y / $h ) * 0.5 );
			$color = imagecolorallocate(
				$img,
				(int) ( $from[0] + ( $to[0] - $from[0] ) * $t ),
				(int) ( $from[1] + ( $to[1] - $from[1] ) * $t ),
				(int) ( $from[2] + ( $to[2] - $from[2] ) * $t )
			);
			imagefilledrectangle( $img, $x, $y, $x, $y + 3, $color );
		}
	}

	$tmp = wp_tempnam( 'wod-seed-' . $post_id . '.png' );
	imagepng( $img, $tmp );

	$attachment = media_handle_sideload(
		array(
			'name'     => 'mission-' . $post_id . '.png',
			'tmp_name' => $tmp,
		),
		$post_id
	);
	if ( is_wp_error( $attachment ) ) {
		WP_CLI::warning( $attachment->get_error_message() );
		return;
	}
	set_post_thumbnail( $post_id, $attachment );
}

foreach ( $wod_posts as $wod_index => $wod_post ) {
	if ( get_page_by_path( $wod_post['slug'], OBJECT, 'post' ) ) {
		WP_CLI::log( "Skipping {$wod_post['slug']} (exists)." );
		continue;
	}

	$wod_cat_ids = array_filter( array_map( 'wod_seed_category', $wod_post['cats'] ) );
	$wod_id      = wp_insert_post(
		array(
			'post_type'     => 'post',
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_name'     => $wod_post['slug'],
			'post_title'    => $wod_post['title'],
			'post_excerpt'  => $wod_post['excerpt'],
			'post_content'  => wod_seed_content( $wod_post, $wod_filler ),
			'post_date'     => $wod_post['date'],
			'post_category' => $wod_cat_ids,
		),
		true
	);
	if ( is_wp_error( $wod_id ) ) {
		WP_CLI::warning( $wod_id->get_error_message() );
		continue;
	}

	// The first category is the mission's topic.
	update_post_meta( $wod_id, '_yoast_wpseo_primary_category', reset( $wod_cat_ids ) );
	update_post_meta(
		$wod_id,
		'wod_quiz',
		array(
			'question' => $wod_post['quiz'][0],
			'options'  => $wod_post['quiz'][1],
			'answer'   => $wod_post['quiz'][2],
		)
	);

	$wod_comments = $wod_post['comments'] ?? array( array( 'Priya Nair', gmdate( 'Y-m-d H:i:s', strtotime( $wod_post['date'] ) + 2 * DAY_IN_SECONDS ), 'Clear and to the point. More of these please.' ) );
	foreach ( $wod_comments as $wod_comment ) {
		$wod_is_author = ! empty( $wod_comment[3] );
		wp_insert_comment(
			array(
				'comment_post_ID'      => $wod_id,
				'comment_author'       => $wod_comment[0],
				'comment_author_email' => $wod_is_author ? get_userdata( 1 )->user_email : sanitize_title( $wod_comment[0] ) . '@example.com',
				'comment_content'      => $wod_comment[2],
				'comment_date'         => $wod_comment[1],
				'comment_date_gmt'     => $wod_comment[1],
				'comment_approved'     => 1,
				'user_id'              => $wod_is_author ? 1 : 0,
			)
		);
	}

	wod_seed_image( $wod_id, $wod_index );
	WP_CLI::success( "Created {$wod_post['slug']} ({$wod_post['read']} min)." );
}

delete_transient( 'wod_posts' );
WP_CLI::success( 'Seed complete.' );
