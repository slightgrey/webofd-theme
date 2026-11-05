<?php
/**
 * Document head. The site header itself is rendered by React.
 *
 * @package Web_Of_D
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#161826">
	<?php
	/*
	 * Set data-theme before first paint so there's no flash of the wrong theme.
	 * A saved choice (localStorage "wod-theme") wins; otherwise follow the OS setting.
	 * inc/minify.php leaves <script> contents untouched.
	 */
	?>
	<script>
	(function () {
		var t;
		try { t = localStorage.getItem('wod-theme'); } catch (e) {}
		if (t !== 'light' && t !== 'dark') {
			t = window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
		}
		document.documentElement.setAttribute('data-theme', t);
		var m = document.querySelector('meta[name="theme-color"]');
		if (m) m.setAttribute('content', t === 'light' ? '#e9e9eb' : '#161826');
	})();
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
