<?php
/**
 * Loads Vite assets: from the dev server when `hot` exists, otherwise from dist/.vite/manifest.json.
 *
 * @package Web_Of_D
 */

defined( 'ABSPATH' ) || exit;

/**
 * Dev server URL written by vite.config.js, or null in production.
 *
 * @return string|null
 */
function wod_vite_dev_url() {
	static $url = false;

	if ( false === $url ) {
		$hot = WOD_DIR . '/hot';
		$url = is_readable( $hot ) ? untrailingslashit( trim( (string) file_get_contents( $hot ) ) ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}

	return $url ? $url : null;
}

/**
 * Parsed build manifest.
 *
 * @return array
 */
function wod_vite_manifest() {
	static $manifest = null;

	if ( null === $manifest ) {
		$path     = WOD_DIR . '/dist/.vite/manifest.json';
		$manifest = is_readable( $path ) ? (array) json_decode( (string) file_get_contents( $path ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}

	return $manifest;
}

/**
 * CSS files for a manifest entry, including those of statically imported chunks.
 *
 * @param string $entry Manifest key, e.g. "src/main.jsx".
 * @param array  $seen  Visited keys (recursion guard).
 * @return string[]
 */
function wod_vite_css( $entry, &$seen = array() ) {
	$manifest = wod_vite_manifest();

	if ( isset( $seen[ $entry ] ) || empty( $manifest[ $entry ] ) ) {
		return array();
	}
	$seen[ $entry ] = true;

	$css = $manifest[ $entry ]['css'] ?? array();
	foreach ( $manifest[ $entry ]['imports'] ?? array() as $import ) {
		$css = array_merge( $css, wod_vite_css( $import, $seen ) );
	}

	return array_unique( $css );
}

/**
 * Enqueue a CSS entry (e.g. "src/admin.css") from the dev server or the build.
 *
 * @param string $handle Style handle.
 * @param string $entry  Manifest key / source path.
 */
function wod_vite_style( $handle, $entry ) {
	$dev = wod_vite_dev_url();
	if ( $dev ) {
		wp_enqueue_style( $handle, $dev . '/' . $entry, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- dev server.
		return;
	}

	$manifest = wod_vite_manifest();
	if ( ! empty( $manifest[ $entry ]['file'] ) ) {
		wp_enqueue_style( $handle, WOD_URI . '/dist/' . $manifest[ $entry ]['file'], array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- hashed filename.
	}
}

add_action( 'wp_enqueue_scripts', 'wod_enqueue_assets' );

/**
 * Every front-end view is rendered by the React app.
 */
function wod_enqueue_assets() {
	if ( wod_vite_dev_url() ) {
		// Dev tags are printed directly in wod_vite_dev_tags().
		return;
	}

	$manifest = wod_vite_manifest();
	$dist     = WOD_URI . '/dist/';

	if ( empty( $manifest['src/main.jsx'] ) ) {
		return;
	}
	foreach ( wod_vite_css( 'src/main.jsx' ) as $i => $file ) {
		wp_enqueue_style( 'wod-app-' . $i, $dist . $file, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- hashed filename.
	}
	wp_enqueue_script_module( 'wod-app', $dist . $manifest['src/main.jsx']['file'], array(), null );
}

add_action( 'wp_head', 'wod_vite_dev_tags', 1 );

/**
 * Print Vite client, React Refresh preamble and entry when the dev server is running.
 */
function wod_vite_dev_tags() {
	$dev = wod_vite_dev_url();
	if ( ! $dev ) {
		return;
	}

	$refresh = esc_url( $dev . '/@react-refresh' );
	?>
<script type="module">
import RefreshRuntime from '<?php echo $refresh; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>';
RefreshRuntime.injectIntoGlobalHook(window);
window.$RefreshReg$ = () => {};
window.$RefreshSig$ = () => (type) => type;
window.__vite_plugin_react_preamble_installed__ = true;
</script>
<script type="module" src="<?php echo esc_url( $dev . '/@vite/client' ); ?>"></script>
<script type="module" src="<?php echo esc_url( $dev . '/src/main.jsx' ); ?>"></script>
	<?php
}

add_action( 'wp_head', 'wod_print_app_data', 0 );

/**
 * Expose content to React. Printed before any module so it is available when the app boots.
 */
function wod_print_app_data() {
	wp_print_inline_script_tag(
		'window.wodData = ' . wp_json_encode( wod_get_app_data(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ) . ';',
		array( 'id' => 'wod-data' )
	);
}
