<?php
/**
 * Search results template.
 *
 * @package Scam_Dev
 */

get_header();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
	<?php scam_dev_breadcrumbs(); ?>

	<header class="mb-8">
		<h1 class="text-4xl font-bold text-white">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: search query. */
					__( 'Результаты поиска: %s', 'scam-dev' ),
					get_search_query()
				)
			);
			?>
		</h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="posts-grid cols-3">
			<?php while ( have_posts() ) : ?>
				<?php the_post(); ?>
				<?php get_template_part( 'template-parts/content', 'search' ); ?>
			<?php endwhile; ?>
		</div>

		<div class="mt-10">
			<?php the_posts_pagination(); ?>
		</div>
	<?php else : ?>
		<div class="text-center py-12">
			<p class="text-gray-400 mb-4">
				<?php esc_html_e( 'Ничего не найдено. Попробуйте другой запрос.', 'scam-dev' ); ?>
			</p>
			<?php get_search_form(); ?>
		</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
