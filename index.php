<?php
/**
 * Every view (board, category, post, page, 404) is rendered by React into #wod-app from window.wodData.
 * The <noscript> block keeps the content readable and crawlable without JavaScript.
 *
 * @package Web_Of_D
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div id="wod-app"></div>
<noscript>
	<main class="mx-auto max-w-[780px] px-6 py-16">
		<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></p>
		<?php if ( is_singular() ) : ?>
			<?php while ( have_posts() ) : ?>
				<?php the_post(); ?>
				<article class="prose-wod">
					<h1><?php the_title(); ?></h1>
					<?php the_content(); ?>
				</article>
			<?php endwhile; ?>
		<?php elseif ( have_posts() ) : ?>
			<?php if ( is_category() ) : ?>
				<h1><?php single_cat_title(); ?></h1>
			<?php endif; ?>
			<ul>
				<?php while ( have_posts() ) : ?>
					<?php the_post(); ?>
					<li class="mb-6">
						<h2 class="text-[19px]"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p class="text-neutral-400"><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></p>
					</li>
				<?php endwhile; ?>
			</ul>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<h1><?php esc_html_e( 'Nothing here', 'web-of-d' ); ?></h1>
		<?php endif; ?>
	</main>
</noscript>
<?php
get_footer();
