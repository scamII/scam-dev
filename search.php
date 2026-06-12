<?php

get_header();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
	<?php scam_dev_breadcrumbs(); ?>
	<div>
			<header class="mb-8">
				<h1 class="text-4xl font-bold text-gray-900">
					<?php printf( esc_html__( 'Результаты поиска: %s', 'scam-dev' ), '<span class="text-blue-600">' . get_search_query() . '</span>' ); ?>
				</h1>
			</header>

			<?php if ( have_posts() ) : ?>
				<div class="posts-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<?php get_template_part( 'template-parts/content', 'search' ); ?>
					<?php endwhile; ?>
				</div>

				<div class="mt-8">
					<?php the_posts_pagination(); ?>
				</div>
			<?php else : ?>
				<div class="text-center py-12">
					<p class="text-gray-600 mb-4">
						<?php esc_html_e( 'Ничего не найдено. Попробуйте другой запрос.', 'scam-dev' ); ?>
					</p>
					<?php get_search_form(); ?>
				</div>
			<?php endif; ?>
		</div>

	</div>
</div>

<?php get_footer(); ?>
