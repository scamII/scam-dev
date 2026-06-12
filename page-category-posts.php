<?php
/*
Template Name: Category Posts
*/
get_header();

$cat_slug = get_post_meta( get_the_ID(), '_cat_slug', true ) ?: 'loshadi';
$cat      = get_category_by_slug( $cat_slug );
$cat_link = $cat ? get_category_link( $cat ) : '#';
?>

<section class="relative overflow-hidden border-b border-slate-500/10">
	<div class="absolute inset-0 bg-gradient-to-b from-coral-500/5 to-transparent"></div>
	<div class="grid-bg absolute inset-0 opacity-50"></div>
	<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20 text-center">
		<h1 class="text-3xl md:text-5xl font-extrabold text-white leading-tight">
			<?php echo $cat ? esc_html( $cat->name ) : get_the_title(); ?>
		</h1>
		<?php if ( $cat && $cat->description ) : ?>
			<p class="mt-4 text-lg text-gray-400 max-w-xl mx-auto"><?php echo esc_html( $cat->description ); ?></p>
		<?php endif; ?>
	</div>
</section>

<section class="py-12 md:py-20">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<?php scam_dev_breadcrumbs(); ?>
		<?php
		$query = new WP_Query( array(
			'category_name'  => $cat_slug,
			'posts_per_page' => 12,
			'paged'          => get_query_var( 'paged' ) ?: 1,
		) );

		if ( $query->have_posts() ) :
			?>
			<div class="posts-grid cols-3">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					?>
					<article class="blog-card group">
						<a href="<?php the_permalink(); ?>" class="block overflow-hidden relative">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php
								the_post_thumbnail(
									'medium_large',
									array(
										'class'   => 'w-full h-56 object-cover group-hover:scale-105 transition-transform duration-700',
										'loading' => 'lazy',
									)
								);
								?>
								<div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
							<?php else : ?>
								<div class="h-56 bg-gradient-to-br from-slate-700/50 to-slate-600/30 flex items-center justify-center">
									<svg class="w-16 h-16 text-slate-500/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
									</svg>
								</div>
							<?php endif; ?>
						</a>
						<div class="p-5">
							<div class="flex items-center gap-3 text-xs text-gray-500 mb-3">
								<time datetime="<?php echo get_the_date( 'c' ); ?>"><?php echo get_the_date(); ?></time>
							</div>
							<h2 class="text-lg font-bold leading-snug mb-2">
								<a href="<?php the_permalink(); ?>" class="text-white group-hover:text-coral-400 transition-colors no-underline"><?php the_title(); ?></a>
							</h2>
							<p class="text-gray-500 text-sm leading-relaxed line-clamp-2">
								<?php echo esc_html( get_the_excerpt() ); ?>
							</p>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<div class="mt-12">
				<?php
				echo paginate_links( array(
					'total'   => $query->max_num_pages,
					'current' => max( 1, get_query_var( 'paged' ) ),
				) );
				?>
			</div>
			<?php
			wp_reset_postdata();
		else :
			?>
			<div class="text-center py-16">
				<p class="text-gray-500">Записей пока нет.</p>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
