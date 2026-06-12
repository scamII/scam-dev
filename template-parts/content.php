<article id="post-<?php the_ID(); ?>" <?php post_class( 'bg-white rounded-lg shadow-sm overflow-hidden' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" class="block">
			<?php the_post_thumbnail( 'medium_large', array( 'class' => 'w-full h-48 object-cover' ) ); ?>
		</a>
	<?php endif; ?>

	<div class="p-6">
		<header class="mb-3">
			<?php if ( is_singular() ) : ?>
				<h2 class="text-2xl font-bold text-gray-900"><?php the_title(); ?></h2>
			<?php else : ?>
				<h2 class="text-2xl font-bold text-gray-900">
					<a href="<?php the_permalink(); ?>" class="hover:text-blue-600 transition-colors">
						<?php the_title(); ?>
					</a>
				</h2>
			<?php endif; ?>

			<div class="mt-2 text-sm text-gray-500">
				<time datetime="<?php echo get_the_date( 'c' ); ?>"><?php echo get_the_date(); ?></time>
				<span class="mx-2">&bull;</span>
				<span><?php echo esc_html( get_the_author() ); ?></span>
			</div>
		</header>

		<div class="text-gray-700">
			<?php the_excerpt(); ?>
		</div>

		<footer class="mt-4">
			<a href="<?php the_permalink(); ?>" class="inline-block text-blue-600 hover:text-blue-800 font-medium">
				<?php esc_html_e( 'Читать далее', 'scam-dev' ); ?> &rarr;
			</a>
		</footer>
	</div>
</article>
