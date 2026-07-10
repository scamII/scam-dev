<?php
/**
 * 404 template.
 *
 * @package Scam_Dev
 */

get_header();
?>

<div class="max-w-3xl mx-auto text-center py-24 px-4">
	<p class="text-7xl font-bold text-coral-400 mb-4" aria-hidden="true">404</p>
	<h1 class="text-3xl font-semibold text-white mb-4">
		<?php esc_html_e( 'Страница не найдена', 'scam-dev' ); ?>
	</h1>
	<p class="text-gray-400 mb-8">
		<?php esc_html_e( 'Запрашиваемая страница не существует или была перемещена.', 'scam-dev' ); ?>
	</p>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"
		class="btn-primary inline-flex px-6 py-3 rounded-lg">
		<?php esc_html_e( 'На главную', 'scam-dev' ); ?>
	</a>
</div>

<?php get_footer(); ?>
