<?php
/**
 * Theme options page: Appearance → Web Of D.
 *
 * All values live in a single `wod_options` array. Fields are defined once in
 * wod_option_fields(), which drives rendering, sanitizing and wod_option() defaults.
 * The page markup uses the `tw:`-prefixed Tailwind build in src/admin.css.
 *
 * @package Web_Of_D
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field definitions, grouped by section.
 *
 * Types: text, url, password, textarea, number (min/max/step), switch, choice (choices), category (slug picker).
 * Optional field keys: help, placeholder. Optional section keys: description, icon (dashicon).
 *
 * @return array<string, array{title: string, fields: array<string, array>}>
 */
function wod_option_fields() {
	return array(
		'hero'     => array(
			'title'       => __( 'Homepage', 'web-of-d' ),
			'description' => __( 'The intro at the top of the mission board.', 'web-of-d' ),
			'icon'        => 'dashicons-admin-home',
			'fields'      => array(
				'hero_eyebrow' => array( 'type' => 'text', 'label' => __( 'Eyebrow', 'web-of-d' ), 'help' => __( 'Small uppercase line above the heading.', 'web-of-d' ), 'default' => 'Vincent Layte · Developer journal' ),
				'hero_heading' => array( 'type' => 'text', 'label' => __( 'Heading', 'web-of-d' ), 'default' => 'Level up on WordPress, WooCommerce and headless React.' ),
				'hero_intro'   => array( 'type' => 'textarea', 'label' => __( 'Intro', 'web-of-d' ), 'default' => 'Every article is a mission. Read to the end for XP, clear the checkpoint quiz for a bonus, and unlock trophies as you go.' ),
			),
		),
		'brand'    => array(
			'title'       => __( 'Brand & links', 'web-of-d' ),
			'description' => __( 'Header logo, the Hire Me link and the footer.', 'web-of-d' ),
			'icon'        => 'dashicons-admin-links',
			'fields'      => array(
				'brand_mark' => array( 'type' => 'text', 'label' => __( 'Logo initials', 'web-of-d' ), 'help' => __( 'One to three characters in the outlined square.', 'web-of-d' ), 'default' => 'VL' ),
				'brand_name' => array( 'type' => 'text', 'label' => __( 'Logo text', 'web-of-d' ), 'help' => __( 'Leave empty to use the site title.', 'web-of-d' ), 'default' => '' ),
				'hire_label' => array( 'type' => 'text', 'label' => __( 'Hire link label', 'web-of-d' ), 'help' => __( 'Leave empty to hide the link.', 'web-of-d' ), 'default' => 'Hire Me' ),
				'hire_url'   => array( 'type' => 'url', 'label' => __( 'Hire link URL', 'web-of-d' ), 'help' => __( 'Opens in a new tab.', 'web-of-d' ), 'placeholder' => 'https://', 'default' => 'https://vincelayte.me' ),
				'owner'      => array( 'type' => 'text', 'label' => __( 'Footer name', 'web-of-d' ), 'help' => __( 'Shown after the © year.', 'web-of-d' ), 'default' => 'Vincent Layte' ),
				'github_url' => array( 'type' => 'url', 'label' => __( 'GitHub URL', 'web-of-d' ), 'help' => __( 'Footer link. Leave empty to hide it.', 'web-of-d' ), 'placeholder' => 'https://github.com/you', 'default' => 'https://github.com' ),
			),
		),
		'game'     => array(
			'title'       => __( 'Game', 'web-of-d' ),
			'description' => __( 'XP, levels and what each post shows. Reader progress is kept in their own browser.', 'web-of-d' ),
			'icon'        => 'dashicons-awards',
			'fields'      => array(
				'xp_per_level'  => array( 'type' => 'number', 'label' => __( 'XP per level', 'web-of-d' ), 'help' => __( 'A post is worth 15 XP per minute of reading.', 'web-of-d' ), 'min' => 100, 'max' => 400, 'step' => 25, 'default' => 150 ),
				'skills_shown'  => array( 'type' => 'number', 'label' => __( 'Skill cards shown', 'web-of-d' ), 'help' => __( 'Categories visible before "Show all".', 'web-of-d' ), 'min' => 3, 'max' => 12, 'step' => 1, 'default' => 5 ),
				'auto_complete' => array( 'type' => 'switch', 'label' => __( 'Clear missions automatically', 'web-of-d' ), 'help' => __( 'Award the post XP when the reader scrolls to the end. Off: they press Complete.', 'web-of-d' ), 'default' => true ),
				'show_quiz'     => array( 'type' => 'switch', 'label' => __( 'Checkpoint quizzes', 'web-of-d' ), 'help' => __( 'Show the quiz card on posts that have one.', 'web-of-d' ), 'default' => true ),
				'show_toc'      => array( 'type' => 'switch', 'label' => __( 'Waypoints', 'web-of-d' ), 'help' => __( 'The table of contents in the post sidebar.', 'web-of-d' ), 'default' => true ),
				'shop_category' => array( 'type' => 'category', 'label' => __( 'Shop category', 'web-of-d' ), 'help' => __( 'Readers clear every post in this category for a quest and a trophy, named after it ("Open shop" and "Shopkeeper" for WooCommerce). Choose None to hide both.', 'web-of-d' ), 'default' => 'woocommerce' ),
			),
		),
		'behavior' => array(
			'title'       => __( 'Navigation', 'web-of-d' ),
			'description' => __( 'How the front end moves between pages.', 'web-of-d' ),
			'icon'        => 'dashicons-controls-forward',
			'fields'      => array(
				'spa' => array( 'type' => 'switch', 'label' => __( 'Instant page changes', 'web-of-d' ), 'help' => __( 'Open posts, categories and pages without a full reload. Turn off to use normal page loads.', 'web-of-d' ), 'default' => true ),
			),
		),
		'recaptcha'   => array(
			'title'       => __( 'reCAPTCHA', 'web-of-d' ),
			'description' => __( 'Spam protection for guest comments and the login screens. Only enforced when both keys are set. Locked out? Add define( \'WOD_RECAPTCHA_DISABLED\', true ); to wp-config.php.', 'web-of-d' ),
			'icon'        => 'dashicons-shield',
			'fields'      => array(
				'recaptcha_version'      => array( 'type' => 'choice', 'label' => __( 'Version', 'web-of-d' ), 'choices' => array( 'v3' => __( 'Invisible (v3)', 'web-of-d' ), 'v2' => __( 'Checkbox (v2)', 'web-of-d' ) ), 'help' => __( 'Must match the key type you created at google.com/recaptcha/admin.', 'web-of-d' ), 'default' => wod_legacy_recaptcha_option( 'vl_cf_recaptcha_version', 'v3' ) ),
				'recaptcha_site'         => array( 'type' => 'text', 'label' => __( 'Site key', 'web-of-d' ), 'default' => wod_legacy_recaptcha_option( 'vl_cf_recaptcha_site', '' ) ),
				'recaptcha_secret'       => array( 'type' => 'password', 'label' => __( 'Secret key', 'web-of-d' ), 'help' => __( 'Leave blank to turn reCAPTCHA off on every form.', 'web-of-d' ), 'default' => wod_legacy_recaptcha_option( 'vl_cf_recaptcha_secret', '' ) ),
				'recaptcha_comments'     => array( 'type' => 'switch', 'label' => __( 'Comments', 'web-of-d' ), 'help' => __( 'Guests must pass reCAPTCHA to comment. Logged-in users skip the check.', 'web-of-d' ), 'default' => '1' === wod_legacy_recaptcha_option( 'vl_recaptcha_comments', '1' ) ),
				'recaptcha_login'        => array( 'type' => 'switch', 'label' => __( 'Login form', 'web-of-d' ), 'help' => __( 'Test it in a private window before logging out.', 'web-of-d' ), 'default' => '1' === wod_legacy_recaptcha_option( 'vl_recaptcha_login', '0' ) ),
				'recaptcha_register'     => array( 'type' => 'switch', 'label' => __( 'Registration form', 'web-of-d' ), 'help' => get_option( 'users_can_register' ) ? '' : __( 'Registration is currently off in Settings → General.', 'web-of-d' ), 'default' => '1' === wod_legacy_recaptcha_option( 'vl_recaptcha_register', '0' ) ),
				'recaptcha_lostpassword' => array( 'type' => 'switch', 'label' => __( 'Lost password form', 'web-of-d' ), 'default' => '1' === wod_legacy_recaptcha_option( 'vl_recaptcha_lostpassword', '0' ) ),
			),
		),
		'performance' => array(
			'title'       => __( 'Performance', 'web-of-d' ),
			'description' => __( 'Site-wide output settings. These do not change how the site looks.', 'web-of-d' ),
			'icon'        => 'dashicons-performance',
			'fields'      => array(
				'minify_html'   => array( 'type' => 'switch', 'label' => __( 'Minify HTML', 'web-of-d' ), 'help' => __( 'Removes comments and extra whitespace from front-end pages (View Source shows compact HTML). Turn off while debugging markup.', 'web-of-d' ), 'default' => true ),
				'disable_emoji' => array( 'type' => 'switch', 'label' => __( 'Disable emoji script', 'web-of-d' ), 'help' => __( 'Removes WordPress\'s emoji script and styles on every page and in wp-admin. Emoji still display, using the visitor\'s own system emoji.', 'web-of-d' ), 'default' => true ),
				'block_styles'  => array( 'type' => 'switch', 'label' => __( 'Remove block styles', 'web-of-d' ), 'help' => __( 'Post content uses the theme\'s own styles, so core block CSS is unused. Turn off if your posts rely on layout blocks such as Columns or Gallery.', 'web-of-d' ), 'default' => true ),
				'clean_head'    => array( 'type' => 'switch', 'label' => __( 'Remove unused <head> tags', 'web-of-d' ), 'help' => __( 'Removes the WordPress version tag, RSD, shortlink, oEmbed and REST API discovery links. Feeds, canonical and the REST API itself keep working.', 'web-of-d' ), 'default' => true ),
			),
		),
	);
}

/**
 * A reCAPTCHA setting saved by the vincelayte React theme, used as this theme's default so
 * existing keys and switches carry over without re-entering them.
 *
 * @param string $name    Option name in the old theme.
 * @param string $default Fallback.
 * @return string
 */
function wod_legacy_recaptcha_option( $name, $default ) {
	return (string) get_option( $name, $default );
}

/**
 * Get one option with its default as fallback.
 *
 * @param string $key Option key.
 * @return mixed
 */
function wod_option( $key ) {
	static $defaults = null;

	if ( null === $defaults ) {
		$defaults = array();
		foreach ( wod_option_fields() as $section ) {
			foreach ( $section['fields'] as $field_key => $field ) {
				$defaults[ $field_key ] = $field['default'];
			}
		}
	}

	$options = get_option( 'wod_options', array() );

	return is_array( $options ) && array_key_exists( $key, $options ) ? $options[ $key ] : ( $defaults[ $key ] ?? '' );
}

add_action( 'admin_menu', 'wod_add_options_page' );

/**
 * Register the options page under Appearance.
 */
function wod_add_options_page() {
	add_theme_page( __( 'Web Of D', 'web-of-d' ), __( 'Web Of D', 'web-of-d' ), 'edit_theme_options', 'web-of-d', 'wod_render_options_page' );
}

add_action( 'admin_init', 'wod_register_settings' );

/**
 * Register the setting. Fields are rendered by wod_render_options_page(), not Settings API tables.
 */
function wod_register_settings() {
	register_setting(
		'wod_options',
		'wod_options',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'wod_sanitize_options',
			'default'           => array(),
		)
	);
}

// The page uses edit_theme_options, so options.php must accept that capability too.
add_filter(
	'option_page_capability_wod_options',
	static function () {
		return 'edit_theme_options';
	}
);

add_action( 'admin_enqueue_scripts', 'wod_options_page_assets' );

/**
 * Tailwind build for the options page only.
 *
 * @param string $hook Admin page hook.
 */
function wod_options_page_assets( $hook ) {
	if ( 'appearance_page_web-of-d' === $hook ) {
		wod_vite_style( 'wod-admin', 'src/admin.css' );
	}
}

/**
 * Sanitize the whole options array according to field types.
 * Must accept its own output: WordPress runs it twice when the option is first created.
 *
 * @param mixed $input Raw input.
 * @return array
 */
function wod_sanitize_options( $input ) {
	$input = is_array( $input ) ? $input : array();
	$clean = array();

	foreach ( wod_option_fields() as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			$raw = $input[ $key ] ?? '';
			$raw = is_scalar( $raw ) ? (string) $raw : '';

			switch ( $field['type'] ) {
				case 'textarea':
					$clean[ $key ] = sanitize_textarea_field( $raw );
					break;

				case 'url':
					$clean[ $key ] = esc_url_raw( trim( $raw ) );
					break;

				case 'number':
					$value         = '' === $raw ? (int) $field['default'] : (int) $raw;
					$clean[ $key ] = max( (int) $field['min'], min( (int) $field['max'], $value ) );
					break;

				case 'switch':
					// Unchecked = not posted = "" (off). A second sanitize pass sees "1" for true.
					$clean[ $key ] = '' !== $raw && '0' !== $raw;
					break;

				case 'category':
					$clean[ $key ] = sanitize_title( $raw );
					break;

				case 'choice':
					$clean[ $key ] = array_key_exists( $raw, $field['choices'] ) ? $raw : $field['default'];
					break;

				default:
					$clean[ $key ] = sanitize_text_field( $raw );
			}
		}
	}

	return $clean;
}

/**
 * Render one field's control.
 *
 * @param string $key   Option key.
 * @param array  $field Field definition.
 */
function wod_render_option_field( $key, $field ) {
	$id          = 'wod_' . $key;
	$name        = 'wod_options[' . $key . ']';
	$value       = wod_option( $key );
	$placeholder = $field['placeholder'] ?? '';
	$input       = 'wod-input tw:block tw:w-full tw:max-w-none tw:rounded-lg tw:border tw:border-black/15 tw:bg-white tw:px-3 tw:py-2 tw:text-sm tw:text-neutral-900 tw:shadow-none tw:transition tw:placeholder:text-neutral-400 tw:hover:border-black/30 tw:focus:border-accent tw:focus:shadow-none tw:focus:ring-4 tw:focus:ring-accent/25';

	switch ( $field['type'] ) {
		case 'textarea':
			printf( '<textarea id="%s" name="%s" rows="3" class="%s">%s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_attr( $input . ' tw:leading-6' ), esc_textarea( $value ) );
			break;

		case 'number':
			printf(
				'<input type="number" id="%s" name="%s" value="%d" min="%d" max="%d" step="%d" class="%s">',
				esc_attr( $id ),
				esc_attr( $name ),
				(int) $value,
				(int) $field['min'],
				(int) $field['max'],
				(int) $field['step'],
				esc_attr( $input . ' tw:max-w-40' )
			);
			break;

		case 'category':
			$terms = get_terms(
				array(
					'taxonomy'   => 'category',
					'hide_empty' => false,
				)
			);
			printf( '<select id="%s" name="%s" class="%s">', esc_attr( $id ), esc_attr( $name ), esc_attr( $input ) );
			printf( '<option value="">%s</option>', esc_html__( '— None (hide the quest) —', 'web-of-d' ) );
			foreach ( is_wp_error( $terms ) ? array() : $terms as $term ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $term->slug ), selected( $value, $term->slug, false ), esc_html( $term->name ) );
			}
			echo '</select>';
			break;

		case 'switch':
			?>
			<span class="tw:relative tw:inline-flex tw:select-none tw:items-center tw:gap-3">
				<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1" class="tw:peer tw:absolute tw:inset-0 tw:z-10 tw:m-0 tw:size-full tw:cursor-pointer tw:opacity-0" <?php checked( (bool) $value ); ?>>
				<span class="tw:block tw:h-6 tw:w-11 tw:rounded-full tw:bg-neutral-300 tw:transition tw:peer-checked:bg-night tw:peer-focus-visible:ring-4 tw:peer-focus-visible:ring-accent/40" aria-hidden="true"></span>
				<span class="tw:absolute tw:top-1 tw:left-1 tw:size-4 tw:rounded-full tw:bg-white tw:shadow tw:transition tw:peer-checked:translate-x-5 tw:peer-checked:bg-accent" aria-hidden="true"></span>
				<span class="tw:hidden tw:w-10 tw:text-xs tw:font-semibold tw:uppercase tw:tracking-wider tw:text-neutral-900 tw:peer-checked:inline"><?php esc_html_e( 'On', 'web-of-d' ); ?></span>
				<span class="tw:inline tw:w-10 tw:text-xs tw:font-semibold tw:uppercase tw:tracking-wider tw:text-neutral-400 tw:peer-checked:hidden"><?php esc_html_e( 'Off', 'web-of-d' ); ?></span>
			</span>
			<?php
			break;

		case 'choice':
			printf( '<select id="%s" name="%s" class="%s">', esc_attr( $id ), esc_attr( $name ), esc_attr( $input ) );
			foreach ( $field['choices'] as $choice => $choice_label ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $choice ), selected( $value, $choice, false ), esc_html( $choice_label ) );
			}
			echo '</select>';
			break;

		case 'password':
			printf( '<input type="password" id="%s" name="%s" value="%s" autocomplete="off" class="%s">', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ), esc_attr( $input ) );
			break;

		case 'url':
			printf( '<input type="url" id="%s" name="%s" value="%s" placeholder="%s" class="%s">', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ), esc_attr( $placeholder ), esc_attr( $input ) );
			break;

		default:
			printf( '<input type="text" id="%s" name="%s" value="%s" placeholder="%s" class="%s">', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ), esc_attr( $placeholder ), esc_attr( $input ) );
	}
}

/**
 * Options page markup.
 */
function wod_render_options_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$sections = wod_option_fields();
	$quizzes  = count(
		array_filter(
			wod_get_post_summaries(),
			static function ( $post ) {
				return ! empty( $post['quiz'] );
			}
		)
	);
	$stats    = array(
		array(
			'label' => __( 'Missions', 'web-of-d' ),
			'count' => (int) wp_count_posts( 'post' )->publish,
			'url'   => admin_url( 'edit.php' ),
		),
		array(
			'label' => __( 'With a quiz', 'web-of-d' ),
			'count' => $quizzes,
			'url'   => admin_url( 'edit.php' ),
		),
		array(
			'label' => __( 'Skill tree categories', 'web-of-d' ),
			'count' => (int) wp_count_terms( array( 'taxonomy' => 'category', 'hide_empty' => true ) ),
			'url'   => admin_url( 'edit-tags.php?taxonomy=category' ),
		),
	);
	$wide     = array( 'textarea' );
	?>
	<div class="wrap wod-admin tw:max-w-5xl tw:pr-5 tw:text-neutral-900">
		<header class="tw:relative tw:mt-4 tw:overflow-hidden tw:rounded-2xl tw:bg-night tw:px-8 tw:py-9 tw:text-white tw:md:px-10">
			<div class="tw:pointer-events-none tw:absolute tw:inset-0 tw:bg-[radial-gradient(700px_300px_at_0%_-10%,rgba(145,132,217,0.35),transparent_70%)]" aria-hidden="true"></div>
			<div class="tw:relative tw:flex tw:flex-col tw:gap-6 tw:lg:flex-row tw:lg:items-end tw:lg:justify-between">
				<div>
					<p class="tw:m-0 tw:text-[11px] tw:uppercase tw:tracking-[0.12em] tw:text-accent"><?php esc_html_e( 'Theme settings', 'web-of-d' ); ?></p>
					<h1 class="tw:m-0 tw:mt-3 tw:p-0 tw:text-4xl tw:font-medium tw:leading-tight tw:tracking-tight tw:text-white"><?php esc_html_e( 'Web Of D', 'web-of-d' ); ?></h1>
					<p class="tw:m-0 tw:mt-3 tw:max-w-xl tw:text-sm tw:leading-6 tw:text-white/60"><?php esc_html_e( 'Homepage text, links and game rules. Posts are the missions: write them as usual, and add a checkpoint quiz in the sidebar of the post editor.', 'web-of-d' ); ?></p>
				</div>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener" class="tw:inline-flex tw:items-center tw:gap-2 tw:self-start tw:rounded-lg tw:border tw:border-accent tw:px-4 tw:py-2 tw:text-sm tw:text-accent tw:no-underline tw:transition tw:hover:bg-accent/15 tw:hover:text-accent tw:focus:shadow-none tw:lg:self-auto">
					<?php esc_html_e( 'View site', 'web-of-d' ); ?> <span class="dashicons dashicons-external tw:size-4 tw:text-base tw:leading-4" aria-hidden="true"></span>
				</a>
			</div>
			<div class="tw:relative tw:mt-8 tw:grid tw:grid-cols-3 tw:gap-2">
				<?php foreach ( $stats as $stat ) : ?>
					<a href="<?php echo esc_url( $stat['url'] ); ?>" class="tw:block tw:rounded-lg tw:bg-white/5 tw:px-4 tw:py-3 tw:text-white tw:no-underline tw:ring-1 tw:ring-white/10 tw:transition tw:hover:bg-white/10 tw:hover:text-white tw:focus:shadow-none">
						<span class="tw:block tw:text-2xl"><?php echo esc_html( number_format_i18n( $stat['count'] ) ); ?></span>
						<span class="tw:block tw:text-xs tw:text-white/50"><?php echo esc_html( $stat['label'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</header>

		<hr class="wp-header-end">
		<?php settings_errors(); ?>

		<form action="options.php" method="post" class="tw:mt-6 tw:flex tw:flex-col tw:gap-5">
			<?php settings_fields( 'wod_options' ); ?>

			<?php foreach ( $sections as $section_key => $section ) : ?>
				<section id="wod-<?php echo esc_attr( $section_key ); ?>" class="tw:overflow-hidden tw:rounded-2xl tw:bg-white tw:shadow-sm tw:ring-1 tw:ring-black/5">
					<div class="tw:flex tw:items-start tw:gap-4 tw:border-b tw:border-black/5 tw:px-6 tw:py-5">
						<span class="tw:flex tw:size-10 tw:shrink-0 tw:items-center tw:justify-center tw:rounded-lg tw:bg-night tw:text-accent">
							<span class="dashicons <?php echo esc_attr( $section['icon'] ?? 'dashicons-admin-generic' ); ?>" aria-hidden="true"></span>
						</span>
						<div>
							<h2 class="tw:m-0 tw:p-0 tw:text-lg tw:font-medium tw:text-neutral-900"><?php echo esc_html( $section['title'] ); ?></h2>
							<?php if ( ! empty( $section['description'] ) ) : ?>
								<p class="tw:m-0 tw:mt-1 tw:text-sm tw:text-neutral-500"><?php echo esc_html( $section['description'] ); ?></p>
							<?php endif; ?>
						</div>
					</div>
					<div class="tw:grid tw:gap-6 tw:px-6 tw:py-6 tw:md:grid-cols-2">
						<?php foreach ( $section['fields'] as $key => $field ) : ?>
							<div class="<?php echo in_array( $field['type'], $wide, true ) ? 'tw:md:col-span-2' : ''; ?>">
								<label for="wod_<?php echo esc_attr( $key ); ?>" class="tw:mb-2 tw:block tw:text-xs tw:font-semibold tw:uppercase tw:tracking-wider tw:text-neutral-700">
									<?php echo esc_html( $field['label'] ); ?>
								</label>
								<?php wod_render_option_field( $key, $field ); ?>
								<?php if ( ! empty( $field['help'] ) ) : ?>
									<p class="tw:m-0 tw:mt-2 tw:text-xs tw:text-neutral-500"><?php echo esc_html( $field['help'] ); ?></p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endforeach; ?>

			<div class="tw:sticky tw:bottom-4 tw:z-10 tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-4 tw:rounded-xl tw:bg-night/95 tw:px-6 tw:py-4 tw:text-white tw:shadow-xl tw:backdrop-blur">
				<p class="tw:m-0 tw:text-sm tw:text-white/70"><?php esc_html_e( 'Changes go live as soon as you save.', 'web-of-d' ); ?></p>
				<button type="submit" name="submit" class="tw:cursor-pointer tw:rounded-lg tw:border tw:border-accent tw:bg-transparent tw:px-6 tw:py-2.5 tw:text-sm tw:font-medium tw:text-accent tw:transition tw:hover:bg-accent/15">
					<?php esc_html_e( 'Save changes', 'web-of-d' ); ?>
				</button>
			</div>
		</form>
	</div>
	<?php
}
