<?php
/**
 * WooCommerce template wrapper.
 *
 * @package Scam_Dev
 */

get_header();
?>

<section class="py-12 md:py-20">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<?php woocommerce_content(); ?>
	</div>
</section>

<?php
get_footer();
