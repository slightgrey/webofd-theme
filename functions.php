<?php
/**
 * Web Of D theme bootstrap.
 *
 * @package Web_Of_D
 */

defined( 'ABSPATH' ) || exit;

define( 'WOD_VERSION', '1.1.0' );
define( 'WOD_DIR', get_template_directory() );
define( 'WOD_URI', get_template_directory_uri() );

require WOD_DIR . '/inc/setup.php';
require WOD_DIR . '/inc/options.php';
require WOD_DIR . '/inc/meta.php';
require WOD_DIR . '/inc/rest.php';
require WOD_DIR . '/inc/data.php';
require WOD_DIR . '/inc/vite.php';
require WOD_DIR . '/inc/cleanup.php';
require WOD_DIR . '/inc/minify.php';
