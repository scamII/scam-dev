<?php
/**
 * Post card.
 *
 * @package Scam_Dev
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'blog-card group' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php echo esc_url( get_permalink() ); ?>" class="block overflow-hidden">
			<?php
			the_post_thumbnail(
				'medium_large',
				array(
					'class'   => 'w-full h-48 object-cover group-hover:scale-105 transition-transform duration-500',
					'loading' => 'lazy',
				)
			);
			?>
		</a>
	<?php endif; ?>

	<div class="p-6">
		<time class="text-xs text-gray-500"
			datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
			<?php echo esc_html( get_the_date() ); ?>
		</time>

		<h2 class="text-xl font-bold text-white mt-2 mb-3">
			<a href="<?php echo esc_url( get_permalink() ); ?>"
				class="hover:text-coral-400 transition-colors">
				<?php echo esc_html( get_the_title() ); ?>
			</a>
		</h2>

		<p class="text-gray-500 text-sm leading-relaxed line-clamp-2">
			<?php echo esc_html( get_the_excerpt() ); ?>
		</p>

		<a href="<?php echo esc_url( get_permalink() ); ?>"
			class="inline-block mt-4 text-coral-400 hover:text-coral-300 font-medium">
			<?php esc_html_e( 'Читать далее', 'scam-dev' ); ?> &rarr;
		</a>
	</div>
</article>
