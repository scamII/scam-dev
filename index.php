<?php
/**
 * Blog index template.
 *
 * @package Scam_Dev
 */

get_header();

$page_title = single_post_title( '', false );
?>

<section class="py-12 md:py-20">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<header class="text-center mb-12">
			<span class="badge-coral">BLOG</span>
			<h1 class="text-3xl md:text-5xl font-extrabold text-white mt-4">
				<?php echo esc_html( $page_title ?: __( 'Блог', 'scam-dev' ) ); ?>
			</h1>
			<?php $description = get_bloginfo( 'description', 'display' ); ?>
			<?php if ( $description ) : ?>
				<p class="mt-3 text-lg text-gray-400 max-w-xl mx-auto">
					<?php echo esc_html( $description ); ?>
				</p>
			<?php endif; ?>
			<div class="divider mx-auto mt-6"></div>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="posts-grid cols-3">
				<?php while ( have_posts() ) : ?>
					<?php
					the_post();
					$categories = get_the_category();
					?>
					<article <?php post_class( 'blog-card group' ); ?>>
						<a href="<?php echo esc_url( get_permalink() ); ?>"
							class="block overflow-hidden relative">
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
							<?php else : ?>
								<div class="h-56 bg-slate-500/10 flex items-center justify-center"
									aria-hidden="true">
									<span class="text-5xl text-gray-600">#</span>
								</div>
							<?php endif; ?>

							<?php if ( $categories ) : ?>
								<span class="absolute top-3 left-3 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-coral-500 text-white shadow-lg">
									<?php echo esc_html( $categories[0]->name ); ?>
								</span>
							<?php endif; ?>
						</a>

						<div class="p-5">
							<div class="flex items-center gap-3 text-xs text-gray-500 mb-3">
								<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
									<?php echo esc_html( get_the_date() ); ?>
								</time>
								<span aria-hidden="true">·</span>
								<span class="text-coral-400/80">
									<?php echo esc_html( scam_dev_reading_time() ); ?>
								</span>
							</div>

							<h2 class="text-lg font-bold leading-snug mb-2">
								<a href="<?php echo esc_url( get_permalink() ); ?>"
									class="text-white group-hover:text-coral-400 transition-colors no-underline">
									<?php echo esc_html( get_the_title() ); ?>
								</a>
							</h2>

							<p class="text-gray-500 text-sm leading-relaxed line-clamp-2">
								<?php echo esc_html( get_the_excerpt() ); ?>
							</p>

							<div class="flex items-center gap-2 mt-4 pt-4 border-t border-slate-500/10">
								<?php
								echo wp_kses_post(
									get_avatar(
										get_the_author_meta( 'ID' ),
										24,
										'',
										'',
										array(
											'class' => 'rounded-full w-6 h-6 ring-1 ring-slate-500/20',
										)
									)
								);
								?>
								<span class="text-xs text-gray-600">
									<?php echo esc_html( get_the_author() ); ?>
								</span>
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
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
