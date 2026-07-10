<?php
/**
 * Single post template.
 *
 * @package Scam_Dev
 */

get_header();

while ( have_posts() ) :
	the_post();
	$categories   = get_the_category();
	$category_url = '';

	if ( $categories ) {
		$category_link = get_category_link( $categories[0]->term_id );
		if ( ! is_wp_error( $category_link ) ) {
			$category_url = $category_link;
		}
	}
	?>
	<section class="relative overflow-hidden border-b border-slate-500/10">
		<div class="grid-bg absolute inset-0 opacity-50" aria-hidden="true"></div>
		<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
			<div class="max-w-3xl mx-auto">
				<?php if ( $categories && $category_url ) : ?>
					<a href="<?php echo esc_url( $category_url ); ?>"
						class="badge-coral mb-4">
						<?php echo esc_html( $categories[0]->name ); ?>
					</a>
				<?php endif; ?>

				<h1 class="text-3xl md:text-5xl font-extrabold text-white leading-tight tracking-tight text-balance">
					<?php echo esc_html( get_the_title() ); ?>
				</h1>

				<div class="flex flex-wrap items-center gap-4 mt-6 text-sm text-gray-400">
					<div class="flex items-center gap-2">
						<?php
						echo wp_kses_post(
							get_avatar(
								get_the_author_meta( 'ID' ),
								28,
								'',
								'',
								array(
									'class' => 'rounded-full w-7 h-7 ring-1 ring-slate-500/20',
								)
							)
						);
						?>
						<span class="text-gray-300">
							<?php echo esc_html( get_the_author() ); ?>
						</span>
					</div>
					<span aria-hidden="true">|</span>
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
						<?php echo esc_html( get_the_date() ); ?>
					</time>
					<?php if ( get_the_modified_time( 'U' ) > get_the_time( 'U' ) ) : ?>
						<span class="text-gray-600 text-xs">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: modified date. */
									__( 'обновлено %s', 'scam-dev' ),
									get_the_modified_date()
								)
							);
							?>
						</span>
					<?php endif; ?>
					<span aria-hidden="true">|</span>
					<span class="text-coral-400/80">
						<?php echo esc_html( scam_dev_reading_time() ); ?>
					</span>
				</div>
			</div>
		</div>
	</section>

	<article <?php post_class(); ?>>
		<div class="max-w-5xl mx-auto px-4 sm:px-6 py-10 md:py-16">
			<?php scam_dev_breadcrumbs(); ?>

			<div class="<?php echo esc_attr( has_post_thumbnail() ? 'flex flex-col md:flex-row gap-8' : '' ); ?>">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="md:w-2/5 shrink-0">
						<?php
						the_post_thumbnail(
							'large',
							array(
								'class'   => 'w-full h-auto rounded-2xl shadow-2xl shadow-black/50',
								'loading' => 'eager',
							)
						);
						?>
					</div>
				<?php endif; ?>

				<div class="min-w-0 flex-1">
					<div id="toc-root"></div>

					<div class="prose prose-lg max-w-none">
						<?php the_content(); ?>
					</div>

					<?php
					wp_link_pages(
						array(
							'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Страницы записи', 'scam-dev' ) . '">',
							'after'  => '</nav>',
						)
					);
					?>

					<?php get_template_part( 'template-parts/donate-inline' ); ?>

					<?php if ( has_tag() ) : ?>
						<div class="mt-10 pt-8 border-t border-slate-500/10">
							<span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
								<?php esc_html_e( 'Теги', 'scam-dev' ); ?>
							</span>
							<div class="flex flex-wrap gap-2 mt-3">
								<?php the_tags( '', ' ', '' ); ?>
							</div>
						</div>
					<?php endif; ?>

					<nav class="mt-10 pt-8 border-t border-slate-500/10"
						aria-label="<?php esc_attr_e( 'Навигация по записям', 'scam-dev' ); ?>">
						<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
							<div>
								<?php $previous_post = get_previous_post(); ?>
								<?php if ( $previous_post ) : ?>
									<span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
										<?php esc_html_e( '← Назад', 'scam-dev' ); ?>
									</span>
									<a href="<?php echo esc_url( get_permalink( $previous_post ) ); ?>"
										class="block mt-1 text-sm font-medium text-gray-300 hover:text-coral-400 transition-colors line-clamp-1">
										<?php echo esc_html( get_the_title( $previous_post ) ); ?>
									</a>
								<?php endif; ?>
							</div>

							<div class="sm:text-right">
								<?php $next_post = get_next_post(); ?>
								<?php if ( $next_post ) : ?>
									<span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
										<?php esc_html_e( 'Вперёд →', 'scam-dev' ); ?>
									</span>
									<a href="<?php echo esc_url( get_permalink( $next_post ) ); ?>"
										class="block mt-1 text-sm font-medium text-gray-300 hover:text-coral-400 transition-colors line-clamp-1">
										<?php echo esc_html( get_the_title( $next_post ) ); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
					</nav>

					<section class="mt-10 p-6 glass flex items-start gap-4"
						aria-label="<?php esc_attr_e( 'Об авторе', 'scam-dev' ); ?>">
						<?php
						echo wp_kses_post(
							get_avatar(
								get_the_author_meta( 'ID' ),
								56,
								'',
								'',
								array(
									'class' => 'rounded-full w-14 h-14 ring-1 ring-slate-500/20 flex-shrink-0',
								)
							)
						);
						?>
						<div>
							<p class="font-semibold text-white">
								<?php echo esc_html( get_the_author() ); ?>
							</p>
							<p class="text-sm text-gray-400 mt-1">
								<?php echo esc_html( get_the_author_meta( 'description' ) ); ?>
							</p>
						</div>
					</section>

					<?php
					$related_posts = new WP_Query(
						array(
							'category__in'        => wp_get_post_categories( get_the_ID() ),
							'posts_per_page'      => 3,
							'post__not_in'        => array( get_the_ID() ),
							'ignore_sticky_posts' => true,
							'no_found_rows'       => true,
						)
					);
					?>

					<?php if ( $related_posts->have_posts() ) : ?>
						<section class="mt-12">
							<h2 class="text-lg font-bold text-white mb-6">
								<span class="text-coral-400" aria-hidden="true">#</span>
								<?php esc_html_e( 'Похожие записи', 'scam-dev' ); ?>
							</h2>
							<div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
								<?php while ( $related_posts->have_posts() ) : ?>
									<?php $related_posts->the_post(); ?>
									<a href="<?php echo esc_url( get_permalink() ); ?>"
										class="card group block no-underline">
										<?php if ( has_post_thumbnail() ) : ?>
											<div class="overflow-hidden">
												<?php
												the_post_thumbnail(
													'medium',
													array(
														'class'   => 'w-full h-36 object-cover group-hover:scale-105 transition-transform duration-500',
														'loading' => 'lazy',
													)
												);
												?>
											</div>
										<?php endif; ?>
										<div class="card-body">
											<h3 class="text-sm font-semibold text-gray-300 group-hover:text-coral-400 transition-colors line-clamp-2">
												<?php echo esc_html( get_the_title() ); ?>
											</h3>
											<time class="text-xs text-gray-600 mt-2 block"
												datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
												<?php echo esc_html( get_the_date() ); ?>
											</time>
										</div>
									</a>
								<?php endwhile; ?>
								<?php wp_reset_postdata(); ?>
							</div>
						</section>
					<?php endif; ?>

					<?php
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</div>
			</div>
		</div>
	</article>
	<?php
endwhile;

get_footer();
