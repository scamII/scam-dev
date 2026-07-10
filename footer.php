</main>

<footer class="site-footer">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
			<section>
				<h2 class="text-lg font-bold text-white mb-4">
					<span class="text-coral-400" aria-hidden="true">&gt;</span>
					<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
				</h2>
				<?php $description = get_bloginfo( 'description', 'display' ); ?>
				<?php if ( $description ) : ?>
					<p class="text-gray-500 text-sm leading-relaxed mb-4">
						<?php echo esc_html( $description ); ?>
					</p>
				<?php endif; ?>

				<div class="flex gap-3 mt-4">
					<a href="<?php echo esc_url( get_theme_mod( 'github_url', 'https://github.com/scamII/scam-dev' ) ); ?>"
						class="social-icon"
						aria-label="<?php esc_attr_e( 'GitHub', 'scam-dev' ); ?>"
						target="_blank" rel="noopener noreferrer">
						<span aria-hidden="true">GH</span>
					</a>
					<a href="<?php echo esc_url( get_theme_mod( 'matrix_url', 'https://chat.scam-dev.ru' ) ); ?>"
						class="social-icon"
						aria-label="<?php esc_attr_e( 'Matrix', 'scam-dev' ); ?>"
						target="_blank" rel="noopener noreferrer">
						<span aria-hidden="true">MX</span>
					</a>
					<a href="<?php echo esc_url( get_feed_link() ); ?>"
						class="social-icon"
						aria-label="<?php esc_attr_e( 'RSS', 'scam-dev' ); ?>">
						<span aria-hidden="true">RSS</span>
					</a>
				</div>
			</section>

			<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
				<div class="lg:col-span-3">
					<?php dynamic_sidebar( 'footer-1' ); ?>
				</div>
			<?php else : ?>
				<section>
					<h2 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">
						<?php esc_html_e( 'Быстрые ссылки', 'scam-dev' ); ?>
					</h2>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'menu_class'     => 'space-y-2 text-sm',
							'container'      => false,
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					?>
				</section>

				<section>
					<h2 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">
						<?php esc_html_e( 'Последние записи', 'scam-dev' ); ?>
					</h2>
					<ul class="space-y-2 text-sm">
						<?php
						$recent_posts = get_posts(
							array(
								'numberposts' => 3,
								'post_status' => 'publish',
							)
						);
						foreach ( $recent_posts as $recent_post ) :
							?>
							<li>
								<a href="<?php echo esc_url( get_permalink( $recent_post ) ); ?>"
									class="text-gray-500 hover:text-coral-400 transition-colors line-clamp-1">
									<?php echo esc_html( get_the_title( $recent_post ) ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>

				<section>
					<h2 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">
						<?php esc_html_e( 'Подписка', 'scam-dev' ); ?>
					</h2>
					<p class="text-gray-500 text-sm mb-4">
						<?php esc_html_e( 'Получайте новые публикации через стандартный RSS-канал без передачи email третьим лицам.', 'scam-dev' ); ?>
					</p>
					<a href="<?php echo esc_url( get_feed_link() ); ?>"
						class="btn-outline inline-flex px-4 py-2 rounded-lg text-sm">
						<?php esc_html_e( 'Открыть RSS', 'scam-dev' ); ?>
					</a>
				</section>
			<?php endif; ?>
		</div>
	</div>

	<div class="border-t border-slate-500/10">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
			<p class="text-center text-gray-600 text-sm">
				&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?>
				<?php echo esc_html( get_bloginfo( 'name' ) ); ?>.
				<?php
				$footer_text = get_theme_mod( 'footer_text', '' );
				if ( $footer_text ) :
					?>
					<span class="text-slate-500/20 mx-2" aria-hidden="true">|</span>
					<?php echo esc_html( $footer_text ); ?>
				<?php endif; ?>
			</p>
		</div>
	</div>

	<div id="back-to-top-root"></div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
