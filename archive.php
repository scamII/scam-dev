<?php
/**
 * Archive template.
 *
 * @package Scam_Dev
 */

get_header();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
	<?php scam_dev_breadcrumbs(); ?>

	<header class="mb-8">
		<h1 class="text-4xl font-bold text-white">
			<?php echo esc_html( get_the_archive_title() ); ?>
		</h1>
		<?php $archive_description = get_the_archive_description(); ?>
		<?php if ( $archive_description ) : ?>
			<div class="mt-3 text-gray-400 prose">
				<?php echo wp_kses_post( $archive_description ); ?>
			</div>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="posts-grid cols-3">
			<?php while ( have_posts() ) : ?>
				<?php the_post(); ?>
				<?php get_template_part( 'template-parts/content' ); ?>
			<?php endwhile; ?>
		</div>

		<div class="mt-10">
			<?php the_posts_pagination(); ?>
		</div>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content', 'none' ); ?>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
