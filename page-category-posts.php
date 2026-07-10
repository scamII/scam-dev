<?php
/**
 * Template Name: Category Posts
 *
 * @package Scam_Dev
 */

get_header();

$category_slug = sanitize_title(
	(string) get_post_meta( get_the_ID(), '_cat_slug', true )
);

if ( ! $category_slug ) {
	$category_slug = 'loshadi';
}

$category = get_category_by_slug( $category_slug );
?>

<section class="relative overflow-hidden border-b border-slate-500/10">
	<div class="grid-bg absolute inset-0 opacity-50" aria-hidden="true"></div>
	<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20 text-center">
		<h1 class="text-3xl md:text-5xl font-extrabold text-white leading-tight">
			<?php echo esc_html( $category ? $category->name : get_the_title() ); ?>
		</h1>
		<?php if ( $category && $category->description ) : ?>
			<p class="mt-4 text-lg text-gray-400 max-w-xl mx-auto">
				<?php echo esc_html( $category->description ); ?>
			</p>
		<?php endif; ?>
	</div>
</section>

<section class="py-12 md:py-20">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<?php scam_dev_breadcrumbs(); ?>

		<?php
		$category_query = new WP_Query(
			array(
				'category_name'  => $category_slug,
				'posts_per_page' => 12,
				'paged'          => max( 1, get_query_var( 'paged' ) ),
			)
		);
		?>

		<?php if ( $category_query->have_posts() ) : ?>
			<div class="posts-grid cols-3">
				<?php while ( $category_query->have_posts() ) : ?>
					<?php $category_query->the_post(); ?>
					<?php get_template_part( 'template-parts/content' ); ?>
				<?php endwhile; ?>
			</div>

			<nav class="mt-12" aria-label="<?php esc_attr_e( 'Пагинация', 'scam-dev' ); ?>">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'total'   => $category_query->max_num_pages,
							'current' => max( 1, get_query_var( 'paged' ) ),
						)
					)
				);
				?>
			</nav>

			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
