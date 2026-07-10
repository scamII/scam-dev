<?php
/**
 * Search result card.
 *
 * @package Scam_Dev
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'card p-6' ); ?>>
	<h2 class="text-xl font-bold text-white">
		<a href="<?php echo esc_url( get_permalink() ); ?>"
			class="hover:text-coral-400 transition-colors">
			<?php echo esc_html( get_the_title() ); ?>
		</a>
	</h2>

	<time class="mt-2 block text-xs text-gray-500"
		datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
		<?php echo esc_html( get_the_date() ); ?>
	</time>

	<p class="mt-3 text-gray-400">
		<?php echo esc_html( get_the_excerpt() ); ?>
	</p>

	<a href="<?php echo esc_url( get_permalink() ); ?>"
		class="inline-block mt-3 text-coral-400 hover:text-coral-300 text-sm font-medium">
		<?php esc_html_e( 'Читать далее', 'scam-dev' ); ?> &rarr;
	</a>
</article>
