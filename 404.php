<?php

get_header();
?>

<div class="text-center py-16">
	<h1 class="text-6xl font-bold text-gray-300 mb-4">404</h1>
	<h2 class="text-2xl font-semibold text-gray-900 mb-4">
		<?php esc_html_e( 'Страница не найдена', 'scam-dev' ); ?>
	</h2>
	<p class="text-gray-600 mb-8">
		<?php esc_html_e( 'Запрашиваемая страница не существует.', 'scam-dev' ); ?>
	</p>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
		<?php esc_html_e( 'На главную', 'scam-dev' ); ?>
	</a>
</div>

<?php get_footer(); ?>
