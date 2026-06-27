<?php
get_header();

while (have_posts()) :
	the_post();
?>

	<section class="relative overflow-hidden border-b border-slate-500/10">
		<div class="absolute inset-0 bg-gradient-to-b from-coral-500/5 to-transparent"></div>
		<div class="grid-bg absolute inset-0 opacity-50"></div>

		<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
			<div class="max-w-3xl mx-auto">
				<?php
				$cats = get_the_category();
				if ($cats) :
				?>
					<a href="<?php echo esc_url(get_category_link($cats[0]->term_id)); ?>"
						class="badge-coral mb-4">
						<?php echo esc_html($cats[0]->name); ?>
					</a>
				<?php endif; ?>

				<h1 class="text-3xl md:text-5xl font-extrabold text-white leading-tight tracking-tight text-balance">
					<?php the_title(); ?>
				</h1>

				<div class="flex flex-wrap items-center gap-4 mt-6 text-sm text-gray-400">
					<div class="flex items-center gap-2">
						<?php echo get_avatar(get_the_author_meta('ID'), 28, '', '', array('class' => 'rounded-full w-7 h-7 ring-1 ring-slate-500/20')); ?>
						<span class="text-gray-300"><?php the_author(); ?></span>
					</div>
					<span class="text-slate-500/20">|</span>
					<time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
					<?php if (get_the_modified_date() !== get_the_date()) : ?>
						<span class="text-gray-600 text-xs">(обн. <?php echo get_the_modified_date(); ?>)</span>
					<?php endif; ?>
					<span class="text-slate-500/20">|</span>
					<span class="text-coral-400/80"><?php echo esc_html(reading_time()); ?></span>
				</div>
			</div>
		</div>
	</section>

	<article>
		<div class="max-w-5xl mx-auto px-4 sm:px-6 py-10 md:py-16">
			<?php scam_dev_breadcrumbs(); ?>
			<div class="<?php echo has_post_thumbnail() ? 'flex flex-col md:flex-row gap-8' : ''; ?>">
				<?php if (has_post_thumbnail()) : ?>
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

					<?php get_template_part('template-parts/donate-inline'); ?>

					<?php if (has_tag()) : ?>
						<div class="mt-10 pt-8 border-t border-slate-500/10">
							<span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
								<?php esc_html_e('Теги', 'scam-dev'); ?>
							</span>
							<div class="flex flex-wrap gap-2 mt-3">
								<?php
								the_tags(
									'<span class="px-3 py-1 bg-slate-500/10 border border-slate-500/20 rounded-full text-sm text-gray-400 hover:text-coral-400 hover:border-coral-500/30 transition-colors">',
									'</span><span class="px-3 py-1 bg-slate-500/10 border border-slate-500/20 rounded-full text-sm text-gray-400 hover:text-coral-400 hover:border-coral-500/30 transition-colors">',
									'</span>'
								);
								?>
							</div>
						</div>
					<?php endif; ?>

					<nav class="mt-10 pt-8 border-t border-slate-500/10">
						<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
							<div>
								<?php
								$prev = get_previous_post();
								if ($prev) :
								?>
									<span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">← <?php esc_html_e('Назад', 'scam-dev'); ?></span>
									<a href="<?php echo esc_url(get_permalink($prev)); ?>"
										class="block mt-1 text-sm font-medium text-gray-300 hover:text-coral-400 transition-colors line-clamp-1">
										<?php echo esc_html(get_the_title($prev)); ?>
									</a>
								<?php endif; ?>
							</div>
							<div class="text-right">
								<?php
								$next = get_next_post();
								if ($next) :
								?>
									<span class="text-xs font-semibold text-gray-500 uppercase tracking-wider"><?php esc_html_e('Вперёд', 'scam-dev'); ?> →</span>
									<a href="<?php echo esc_url(get_permalink($next)); ?>"
										class="block mt-1 text-sm font-medium text-gray-300 hover:text-coral-400 transition-colors line-clamp-1">
										<?php echo esc_html(get_the_title($next)); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
					</nav>

					<div class="mt-10 p-6 glass flex items-start gap-4">
						<?php echo get_avatar(get_the_author_meta('ID'), 56, '', '', array('class' => 'rounded-full w-14 h-14 ring-1 ring-slate-500/20 flex-shrink-0')); ?>
						<div>
							<p class="font-semibold text-white"><?php the_author(); ?></p>
							<p class="text-sm text-gray-400 mt-1"><?php echo esc_html(get_the_author_meta('description')); ?></p>
						</div>
					</div>

					<?php
					$related = new WP_Query(
						array(
							'category__in'        => wp_get_post_categories(get_the_ID()),
							'posts_per_page'      => 3,
							'post__not_in'        => array(get_the_ID()),
							'ignore_sticky_posts' => true,
						)
					);

					if ($related->have_posts()) :
					?>
						<div class="mt-12">
							<h3 class="text-lg font-bold text-white mb-6">
								<span class="text-coral-400">#</span> <?php esc_html_e('Похожие записи', 'scam-dev'); ?>
							</h3>
							<div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
								<?php
								while ($related->have_posts()) :
									$related->the_post();
								?>
									<a href="<?php the_permalink(); ?>" class="card group block no-underline">
										<?php if (has_post_thumbnail()) : ?>
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
											<h4 class="text-sm font-semibold text-gray-300 group-hover:text-coral-400 transition-colors line-clamp-2">
												<?php the_title(); ?>
											</h4>
											<time class="text-xs text-gray-600 mt-2 block"><?php echo get_the_date(); ?></time>
										</div>
									</a>
								<?php
								endwhile;
								wp_reset_postdata();
								?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div id="related-posts-root" data-post-id="<?php the_ID(); ?>"></div>
	</article>

<?php
endwhile;

get_footer();
