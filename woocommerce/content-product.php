<?php
/**
 * Product card in loop.
 *
 * @package Scam_Dev
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, 'WC_Product' ) ) {
	return;
}
?>
<article <?php wc_product_class( 'blog-card group', $product ); ?>>
	<a href="<?php the_permalink(); ?>" class="block overflow-hidden relative">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'medium_large', array(
				'class'   => 'w-full h-56 object-cover group-hover:scale-105 transition-transform duration-700',
				'loading' => 'lazy',
			) ); ?>
			<div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
		<?php else : ?>
			<div class="h-56 bg-gradient-to-br from-slate-700/50 to-slate-600/30 flex items-center justify-center">
				<?php echo wp_kses_post( wc_placeholder_img( 'medium_large' ) ); ?>
			</div>
		<?php endif; ?>
	</a>

	<div class="p-5">
		<div class="text-xs text-coral-400/80 mb-2">
			<?php echo wp_kses_post( wc_get_product_category_list( $product->get_id(), ', ' ) ); ?>
		</div>
		<h2 class="text-lg font-bold leading-snug mb-1">
			<a href="<?php the_permalink(); ?>" class="text-white group-hover:text-coral-400 transition-colors no-underline">
				<?php the_title(); ?>
			</a>
		</h2>
		<div class="text-coral-400 font-bold text-lg mt-2">
			<?php echo wp_kses_post( $product->get_price_html() ); ?>
		</div>
		<div class="mt-3">
			<?php woocommerce_template_loop_add_to_cart( array( 'class' => 'btn-primary w-full text-sm px-4 py-2' ) ); ?>
		</div>
	</div>
</article>
