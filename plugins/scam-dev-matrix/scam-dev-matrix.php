<?php
/**
 * Plugin Name: Scam Dev Matrix
 * Description: Защищённая регистрация пользователей Matrix и кешируемый виджет состояния сервера.
 * Version: 1.3.0
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Author: Scam Dev
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: scam-dev-matrix
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SCAM_DEV_MATRIX_VERSION', '1.3.0' );
define( 'SCAM_DEV_MATRIX_PATH', plugin_dir_path( __FILE__ ) );
define( 'SCAM_DEV_MATRIX_URL', plugin_dir_url( __FILE__ ) );
define( 'SCAM_DEV_MATRIX_TEMPLATE', 'scam-dev-matrix-register.php' );

require_once __DIR__ . '/class-matrix-register.php';
require_once __DIR__ . '/class-matrix-stats-widget.php';

/**
 * Register the plugin page template in the editor.
 *
 * @param array $templates Existing templates.
 * @return array
 */
function scam_dev_matrix_page_templates( $templates ) {
	$templates[ SCAM_DEV_MATRIX_TEMPLATE ] = __(
		'Matrix Registration',
		'scam-dev-matrix'
	);
	return $templates;
}
add_filter( 'theme_page_templates', 'scam_dev_matrix_page_templates' );

/**
 * Serve the plugin-owned template.
 *
 * @param string $template Theme-selected template.
 * @return string
 */
function scam_dev_matrix_template_include( $template ) {
	$registration_page = Scam_Dev_Matrix_Registration_Service::get_registration_page();

	if ( $registration_page && is_page( $registration_page->ID ) ) {
		$plugin_template = SCAM_DEV_MATRIX_PATH . 'templates/page-matrix-register.php';

		if ( is_readable( $plugin_template ) ) {
			return $plugin_template;
		}
	}

	return $template;
}
add_filter( 'template_include', 'scam_dev_matrix_template_include' );

/**
 * Enqueue plugin-owned frontend assets.
 */
function scam_dev_matrix_assets() {
	$registration_page = is_page_template( SCAM_DEV_MATRIX_TEMPLATE );
	$widget_active     = is_active_widget(
		false,
		false,
		'scamdev_matrix_stats',
		true
	);

	if ( ! $registration_page && ! $widget_active ) {
		return;
	}

	$style_path = SCAM_DEV_MATRIX_PATH . 'assets/matrix.css';

	wp_enqueue_style(
		'scam-dev-matrix',
		SCAM_DEV_MATRIX_URL . 'assets/matrix.css',
		array(),
		is_readable( $style_path ) ? filemtime( $style_path ) : SCAM_DEV_MATRIX_VERSION
	);

	if ( $registration_page ) {
		$script_path = SCAM_DEV_MATRIX_PATH . 'assets/matrix.js';

		wp_enqueue_script(
			'scam-dev-matrix',
			SCAM_DEV_MATRIX_URL . 'assets/matrix.js',
			array(),
			is_readable( $script_path ) ? filemtime( $script_path ) : SCAM_DEV_MATRIX_VERSION,
			true
		);
		wp_script_add_data( 'scam-dev-matrix', 'strategy', 'defer' );

		if ( Scam_Dev_Matrix_Registration_Service::turnstile_is_configured() ) {
			wp_enqueue_script(
				'cloudflare-turnstile',
				'https://challenges.cloudflare.com/turnstile/v0/api.js',
				array(),
				null,
				true
			);
			wp_script_add_data( 'cloudflare-turnstile', 'strategy', 'defer' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'scam_dev_matrix_assets' );

register_activation_hook(
	__FILE__,
	array( 'Scam_Dev_Matrix_Registration_Service', 'activate' )
);

Scam_Dev_Matrix_Registration_Service::init();
