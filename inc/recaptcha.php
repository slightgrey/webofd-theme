<?php
/**
 * Google reCAPTCHA (v2 checkbox or v3 invisible) for guest comments and the wp-login.php
 * login, registration and lost-password forms. Settings: Appearance → Web Of D → reCAPTCHA.
 *
 * Ported from the vincelayte React theme; its saved keys are used as defaults (inc/options.php).
 *
 * Emergency kill switch (e.g. locked out by a bad key): add
 * define( 'WOD_RECAPTCHA_DISABLED', true ); to wp-config.php.
 *
 * @package Web_Of_D
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether reCAPTCHA is enforced on a form: comments, login, register or lostpassword.
 * Never enforced unless both keys are set.
 *
 * @param string $form Form key.
 * @return bool
 */
function wod_recaptcha_enabled_for( $form ) {
	if ( defined( 'WOD_RECAPTCHA_DISABLED' ) && WOD_RECAPTCHA_DISABLED ) {
		return false;
	}
	if ( ! wod_option( 'recaptcha_site' ) || ! wod_option( 'recaptcha_secret' ) ) {
		return false;
	}
	return (bool) wod_option( 'recaptcha_' . $form );
}

/**
 * Configured version, v2 or v3.
 *
 * @return string
 */
function wod_recaptcha_version() {
	return 'v2' === wod_option( 'recaptcha_version' ) ? 'v2' : 'v3';
}

/**
 * What the comment form needs (never the secret). Read by src/lib/recaptcha.js.
 *
 * @return array{version: string, siteKey: string, comments: bool}
 */
function wod_recaptcha_client_config() {
	return array(
		'version'  => wod_recaptcha_version(),
		'siteKey'  => (string) wod_option( 'recaptcha_site' ),
		'comments' => wod_recaptcha_enabled_for( 'comments' ),
	);
}

/**
 * Check a token with Google. v3 tokens also need a score of at least 0.5.
 *
 * @param string $token Token from the browser.
 * @return true|WP_Error
 */
function wod_verify_recaptcha( $token ) {
	if ( ! $token ) {
		return new WP_Error( 'recaptcha_missing', __( 'Please complete the reCAPTCHA.', 'web-of-d' ), array( 'status' => 400 ) );
	}

	$response = wp_remote_post(
		'https://www.google.com/recaptcha/api/siteverify',
		array(
			'timeout' => 10,
			'body'    => array(
				'secret'   => (string) wod_option( 'recaptcha_secret' ),
				'response' => $token,
			),
		)
	);
	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'recaptcha_error', __( 'reCAPTCHA verification failed.', 'web-of-d' ), array( 'status' => 500 ) );
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( empty( $body['success'] ) ) {
		return new WP_Error( 'recaptcha_failed', __( 'reCAPTCHA verification failed.', 'web-of-d' ), array( 'status' => 400 ) );
	}
	if ( 'v3' === wod_recaptcha_version() && isset( $body['score'] ) && $body['score'] < 0.5 ) {
		return new WP_Error( 'recaptcha_score', __( 'reCAPTCHA score too low.', 'web-of-d' ), array( 'status' => 400 ) );
	}

	return true;
}

/**
 * Token posted by a classic form (comments, wp-login.php).
 *
 * @return string
 */
function wod_recaptcha_post_token() {
	return sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
}

// ---------------------------------------------------------------------------
// Comments
// ---------------------------------------------------------------------------

add_filter( 'rest_pre_insert_comment', 'wod_recaptcha_rest_comment', 10, 2 );

/**
 * The theme's comment form posts to the REST API with a `recaptcha_token`.
 *
 * @param array|WP_Error  $prepared_comment Comment data.
 * @param WP_REST_Request $request          Request.
 * @return array|WP_Error
 */
function wod_recaptcha_rest_comment( $prepared_comment, $request ) {
	if ( is_wp_error( $prepared_comment ) || is_user_logged_in() || ! wod_recaptcha_enabled_for( 'comments' ) ) {
		return $prepared_comment;
	}

	$verify = wod_verify_recaptcha( sanitize_text_field( (string) $request->get_param( 'recaptcha_token' ) ) );
	return is_wp_error( $verify ) ? $verify : $prepared_comment;
}

add_action( 'pre_comment_on_post', 'wod_recaptcha_classic_comment' );

/**
 * The classic wp-comments-post.php endpoint. The theme never renders that form, so this is
 * where bots post directly.
 */
function wod_recaptcha_classic_comment() {
	if ( is_user_logged_in() || ! wod_recaptcha_enabled_for( 'comments' ) ) {
		return;
	}

	$verify = wod_verify_recaptcha( wod_recaptcha_post_token() );
	if ( is_wp_error( $verify ) ) {
		wp_die( esc_html( $verify->get_error_message() ), esc_html__( 'Comment Submission Failure', 'web-of-d' ), array( 'response' => 403, 'back_link' => true ) );
	}
}

// ---------------------------------------------------------------------------
// wp-login.php: login, register, lost password
// ---------------------------------------------------------------------------

add_action( 'login_form', 'wod_recaptcha_login_widget_login' );
add_action( 'register_form', 'wod_recaptcha_login_widget_register' );
add_action( 'lostpassword_form', 'wod_recaptcha_login_widget_lostpassword' );

/** Login form widget. */
function wod_recaptcha_login_widget_login() {
	wod_recaptcha_login_widget( 'login' );
}

/** Registration form widget. */
function wod_recaptcha_login_widget_register() {
	wod_recaptcha_login_widget( 'register' );
}

/** Lost password form widget. */
function wod_recaptcha_login_widget_lostpassword() {
	wod_recaptcha_login_widget( 'lostpassword' );
}

/**
 * Print the reCAPTCHA field for a wp-login.php form.
 *
 * @param string $form Form key.
 */
function wod_recaptcha_login_widget( $form ) {
	if ( ! wod_recaptcha_enabled_for( $form ) ) {
		return;
	}

	$site_key = (string) wod_option( 'recaptcha_site' );

	if ( 'v2' === wod_recaptcha_version() ) {
		// The checkbox is 304px wide; the login form only has ~272px, so scale it down.
		printf(
			'<div class="g-recaptcha" data-sitekey="%s" style="transform:scale(0.88);transform-origin:0 0;height:70px;margin-bottom:16px"></div>
			<script src="https://www.google.com/recaptcha/api.js" async defer></script>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
			esc_attr( $site_key )
		);
		return;
	}

	// v3: fetch a fresh token on submit (tokens expire after two minutes), then submit for real.
	$input_id = 'wod-recaptcha-' . $form;
	printf(
		'<input type="hidden" name="g-recaptcha-response" id="%1$s">
		<script src="https://www.google.com/recaptcha/api.js?render=%2$s"></script>
		<script>
		(function () {
			var input = document.getElementById(%3$s);
			var form = input.form;
			form.addEventListener("submit", function (e) {
				if (input.value) return;
				e.preventDefault();
				grecaptcha.ready(function () {
					grecaptcha.execute(%4$s, { action: %5$s }).then(function (token) {
						input.value = token;
						HTMLFormElement.prototype.submit.call(form);
					});
				});
			});
		})();
		</script>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		esc_attr( $input_id ),
		esc_attr( $site_key ),
		wp_json_encode( $input_id ),
		wp_json_encode( $site_key ),
		wp_json_encode( $form )
	);
}

/**
 * Whether this is a form POST to wp-login.php.
 *
 * @return bool
 */
function wod_recaptcha_is_login_request() {
	return 'wp-login.php' === ( $GLOBALS['pagenow'] ?? '' ) && 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' );
}

/**
 * Login-screen error markup.
 *
 * @param WP_Error $error Error.
 * @return string
 */
function wod_recaptcha_login_error( $error ) {
	return '<strong>' . esc_html__( 'Error:', 'web-of-d' ) . '</strong> ' . esc_html( $error->get_error_message() );
}

// Priority 30: after wp_authenticate_username_password (20), which would otherwise overwrite the error.
add_filter( 'authenticate', 'wod_recaptcha_authenticate', 30 );

/**
 * Check the login form. Only the wp-login.php form: not XML-RPC, REST or application passwords.
 *
 * @param WP_User|WP_Error|null $user Result so far.
 * @return WP_User|WP_Error|null
 */
function wod_recaptcha_authenticate( $user ) {
	if ( ! wod_recaptcha_is_login_request() || ! isset( $_POST['log'] ) || ! wod_recaptcha_enabled_for( 'login' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return $user;
	}

	$verify = wod_verify_recaptcha( wod_recaptcha_post_token() );
	return is_wp_error( $verify ) ? new WP_Error( $verify->get_error_code(), wod_recaptcha_login_error( $verify ) ) : $user;
}

add_filter( 'registration_errors', 'wod_recaptcha_registration_errors' );

/**
 * Check the registration form.
 *
 * @param WP_Error $errors Registration errors.
 * @return WP_Error
 */
function wod_recaptcha_registration_errors( $errors ) {
	if ( wod_recaptcha_is_login_request() && wod_recaptcha_enabled_for( 'register' ) ) {
		$verify = wod_verify_recaptcha( wod_recaptcha_post_token() );
		if ( is_wp_error( $verify ) ) {
			$errors->add( $verify->get_error_code(), wod_recaptcha_login_error( $verify ) );
		}
	}
	return $errors;
}

// Also fires for the admin "Send password reset" action, hence the wp-login.php check.
add_action( 'lostpassword_post', 'wod_recaptcha_lostpassword' );

/**
 * Check the lost-password form.
 *
 * @param WP_Error $errors Errors.
 */
function wod_recaptcha_lostpassword( $errors ) {
	if ( wod_recaptcha_is_login_request() && wod_recaptcha_enabled_for( 'lostpassword' ) ) {
		$verify = wod_verify_recaptcha( wod_recaptcha_post_token() );
		if ( is_wp_error( $verify ) ) {
			$errors->add( $verify->get_error_code(), wod_recaptcha_login_error( $verify ) );
		}
	}
}
