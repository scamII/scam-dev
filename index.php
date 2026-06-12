<?php
get_header();
?>

<section class="py-12 md:py-20">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="text-center mb-12">
			<span class="badge-coral mb-4">BLOG</span>
			<h1 class="text-3xl md:text-5xl font-extrabold text-white mt-4">
				<?php single_post_title(); ?>
			</h1>
			<?php
			$description = get_bloginfo( 'description', 'display' );
			if ( $description ) :
				?>
				<p class="mt-3 text-lg text-gray-400 max-w-xl mx-auto">
					<?php echo esc_html( $description ); ?>
				</p>
			<?php endif; ?>
			<div class="divider mx-auto mt-6"></div>
		</div>

		<?php if ( have_posts() ) : ?>
			<div class="posts-grid cols-3">
				<?php
				while ( have_posts() ) :
					the_post();
					$cats = get_the_category();
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
							<?php if ( $cats ) : ?>
								<span class="absolute top-3 left-3 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-coral-500 text-white shadow-lg">
									<?php echo esc_html( $cats[0]->name ); ?>
								</span>
							<?php endif; ?>
						</a>
						<div class="p-5">
							<div class="flex items-center gap-3 text-xs text-gray-500 mb-3">
								<time datetime="<?php echo get_the_date( 'c' ); ?>"><?php echo get_the_date(); ?></time>
								<span class="text-slate-500/20">·</span>
								<span class="text-coral-400/80"><?php echo esc_html( reading_time() ); ?></span>
							</div>
							<h2 class="text-lg font-bold leading-snug mb-2">
								<a href="<?php the_permalink(); ?>" class="text-white group-hover:text-coral-400 transition-colors no-underline">
									<?php the_title(); ?>
								</a>
							</h2>
							<p class="text-gray-500 text-sm leading-relaxed line-clamp-2">
								<?php echo esc_html( get_the_excerpt() ); ?>
							</p>
							<div class="flex items-center gap-2 mt-4 pt-4 border-t border-slate-500/10">
								<?php echo get_avatar( get_the_author_meta( 'ID' ), 24, '', '', array( 'class' => 'rounded-full w-6 h-6 ring-1 ring-slate-500/20' ) ); ?>
								<span class="text-xs text-gray-600"><?php the_author(); ?></span>
							</div>
						</div>
					</article>
				<?php endwhile; ?>
			</div>

			<div class="mt-12">
				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 2,
						'prev_text' => __( '← Назад', 'scam-dev' ),
						'next_text' => __( 'Вперёд →', 'scam-dev' ),
					)
				);
				?>
			</div>

		<?php else : ?>
			<div class="text-center py-16">
				<div class="w-20 h-20 bg-slate-500/10 rounded-full flex items-center justify-center mx-auto mb-6">
					<svg class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
					</svg>
				</div>
				<h2 class="text-xl font-bold text-white mb-2">
					<?php esc_html_e( 'Записи не найдены', 'scam-dev' ); ?>
				</h2>
				<p class="text-gray-500">
					<?php esc_html_e( 'Записей пока нет.', 'scam-dev' ); ?>
				</p>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
