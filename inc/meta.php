<?php
/**
 * Checkpoint quiz: the `wod_quiz` post meta and its native meta box.
 *
 * Shape: { question: string, options: string[3], answer: int 0-2 }.
 *
 * @package Web_Of_D
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'wod_register_meta' );

/**
 * Register `wod_quiz` and expose it in the REST API.
 */
function wod_register_meta() {
	register_post_meta(
		'post',
		'wod_quiz',
		array(
			'type'              => 'object',
			'single'            => true,
			'default'           => array(),
			'sanitize_callback' => 'wod_sanitize_quiz',
			'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
				return current_user_can( 'edit_post', $post_id );
			},
			'show_in_rest'      => array(
				'schema' => array(
					'type'                 => 'object',
					'additionalProperties' => false,
					'properties'           => array(
						'question' => array( 'type' => 'string' ),
						'options'  => array(
							'type'     => 'array',
							'items'    => array( 'type' => 'string' ),
							'maxItems' => 3,
						),
						'answer'   => array(
							'type'    => 'integer',
							'minimum' => 0,
							'maximum' => 2,
						),
					),
				),
			),
		)
	);
}

/**
 * Normalise a quiz array. Returns an empty array when there is no question.
 *
 * @param mixed $quiz Raw value.
 * @return array
 */
function wod_sanitize_quiz( $quiz ) {
	$quiz     = is_array( $quiz ) ? $quiz : array();
	$question = sanitize_text_field( (string) ( $quiz['question'] ?? '' ) );
	if ( '' === $question ) {
		return array();
	}

	$options = array();
	for ( $i = 0; $i < 3; $i++ ) {
		$options[] = sanitize_text_field( (string) ( $quiz['options'][ $i ] ?? '' ) );
	}

	return array(
		'question' => $question,
		'options'  => $options,
		'answer'   => min( 2, absint( $quiz['answer'] ?? 0 ) ),
	);
}

/**
 * A quiz the front end can show: a question plus three non-empty options.
 *
 * @param int $post_id Post ID.
 * @return array|null
 */
function wod_get_quiz( $post_id ) {
	$quiz = wod_sanitize_quiz( get_post_meta( $post_id, 'wod_quiz', true ) );
	if ( ! $quiz || in_array( '', $quiz['options'], true ) ) {
		return null;
	}
	return $quiz;
}

add_action( 'add_meta_boxes_post', 'wod_add_quiz_meta_box' );

/**
 * Register the meta box on the post editor.
 */
function wod_add_quiz_meta_box() {
	add_meta_box( 'wod_quiz', __( 'Checkpoint quiz', 'web-of-d' ), 'wod_render_quiz_meta_box', 'post', 'side' );
}

/**
 * Meta box markup.
 *
 * @param WP_Post $post Current post.
 */
function wod_render_quiz_meta_box( $post ) {
	$quiz = wod_sanitize_quiz( get_post_meta( $post->ID, 'wod_quiz', true ) );
	wp_nonce_field( 'wod_quiz_save', 'wod_quiz_nonce' );
	?>
	<div class="tw:flex tw:flex-col tw:gap-3">
		<p class="tw:m-0 tw:text-xs tw:text-gray-600"><?php esc_html_e( 'One question shown at the end of the post. Leave the question empty to hide the quiz.', 'web-of-d' ); ?></p>
		<p class="tw:m-0">
			<label class="tw:mb-1 tw:block tw:font-semibold" for="wod-quiz-question"><?php esc_html_e( 'Question', 'web-of-d' ); ?></label>
			<input type="text" class="widefat" id="wod-quiz-question" name="wod_quiz[question]" value="<?php echo esc_attr( $quiz['question'] ?? '' ); ?>">
		</p>
		<?php foreach ( array( 'A', 'B', 'C' ) as $i => $key ) : ?>
			<p class="tw:m-0">
				<label class="tw:mb-1 tw:block" for="wod-quiz-option-<?php echo esc_attr( $i ); ?>">
					<?php
					/* translators: %s: option letter (A, B or C). */
					printf( esc_html__( 'Option %s', 'web-of-d' ), esc_html( $key ) );
					?>
				</label>
				<input type="text" class="widefat" id="wod-quiz-option-<?php echo esc_attr( $i ); ?>" name="wod_quiz[options][]" value="<?php echo esc_attr( $quiz['options'][ $i ] ?? '' ); ?>">
			</p>
		<?php endforeach; ?>
		<p class="tw:m-0">
			<label class="tw:mb-1 tw:block tw:font-semibold" for="wod-quiz-answer"><?php esc_html_e( 'Correct answer', 'web-of-d' ); ?></label>
			<select id="wod-quiz-answer" name="wod_quiz[answer]">
				<?php foreach ( array( 'A', 'B', 'C' ) as $i => $key ) : ?>
					<option value="<?php echo esc_attr( $i ); ?>" <?php selected( (int) ( $quiz['answer'] ?? 0 ), $i ); ?>><?php echo esc_html( $key ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
	</div>
	<?php
}

add_action( 'save_post_post', 'wod_save_quiz_meta_box' );

/**
 * Save the meta box. REST saves carry no nonce, so they are skipped here.
 *
 * @param int $post_id Post ID.
 */
function wod_save_quiz_meta_box( $post_id ) {
	if ( ! isset( $_POST['wod_quiz_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['wod_quiz_nonce'] ) ), 'wod_quiz_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Each field is sanitised in wod_sanitize_quiz().
	$quiz = wod_sanitize_quiz( isset( $_POST['wod_quiz'] ) ? wp_unslash( $_POST['wod_quiz'] ) : array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	if ( $quiz ) {
		update_post_meta( $post_id, 'wod_quiz', $quiz );
	} else {
		delete_post_meta( $post_id, 'wod_quiz' );
	}
}

add_action( 'admin_enqueue_scripts', 'wod_quiz_meta_box_assets' );

/**
 * Meta box styles (tw:-prefixed Tailwind, built from src/admin.css).
 *
 * @param string $hook Admin page hook.
 */
function wod_quiz_meta_box_assets( $hook ) {
	$screen = get_current_screen();
	if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && $screen && 'post' === $screen->post_type ) {
		wod_vite_style( 'wod-admin', 'src/admin.css' );
	}
}
