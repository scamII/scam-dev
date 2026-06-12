<article id="post-<?php the_ID(); ?>" <?php post_class( 'bg-white rounded-lg shadow-sm p-6' ); ?>>
	<header class="mb-3">
		<h2 class="text-xl font-bold text-gray-900">
			<a href="<?php the_permalink(); ?>" class="hover:text-blue-600 transition-colors">
				<?php the_title(); ?>
			</a>
		</h2>
		<div class="mt-1 text-sm text-gray-500">
			<time datetime="<?php echo get_the_date( 'c' ); ?>"><?php echo get_the_date(); ?></time>
		</div>
	</header>

	<div class="text-gray-700">
		<?php the_excerpt( array( 'limit' => 30 ) ); ?>
	</div>

	<a href="<?php the_permalink(); ?>" class="inline-block mt-3 text-blue-600 hover:text-blue-800 text-sm font-medium">
		<?php esc_html_e( 'Читать далее', 'scam-dev' ); ?> &rarr;
	</a>
</article>
