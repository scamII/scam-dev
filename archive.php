<?php

get_header();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
	<?php scam_dev_breadcrumbs(); ?>
	<div>
			<header class="mb-8">
				<h1 class="text-4xl font-bold text-gray-900">
					<?php
					if ( is_category() ) {
						single_cat_title();
					} elseif ( is_tag() ) {
						single_tag_title( esc_html__( 'Метка: ', 'scam-dev' ) );
					} elseif ( is_author() ) {
						esc_html_e( 'Автор: ', 'scam-dev' );
						the_author();
					} elseif ( is_year() ) {
						echo get_the_date( 'Y' );
					} elseif ( is_month() ) {
						echo get_the_date( 'F Y' );
					} else {
						esc_html_e( 'Архивы', 'scam-dev' );
					}
					?>
				</h1>
			</header>

			<?php if ( have_posts() ) : ?>
				<div class="posts-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<?php get_template_part( 'template-parts/content', get_post_type() ); ?>
					<?php endwhile; ?>
				</div>

				<div class="mt-8">
					<?php the_posts_pagination(); ?>
				</div>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>
		</div>

	</div>
</div>

<?php get_footer(); ?>
