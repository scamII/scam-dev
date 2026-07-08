<?php
/**
 * Plugin Name: Scam Dev Matrix
 * Description: Регистрация пользователей в Matrix-чате, виджет статистики сервера и навигация.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Author: Scam Dev
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: scam-dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SCAM_DEV_MATRIX_PATH', plugin_dir_path( __FILE__ ) );

require_once __DIR__ . '/class-matrix-register.php';
require_once __DIR__ . '/class-matrix-stats-widget.php';

// Serve registration template from plugin directory.
add_filter(
	'template_include',
	function ( $template ) {
		if ( is_page_template( 'page-matrix-register.php' ) ) {
			$plugin_template = SCAM_DEV_MATRIX_PATH . 'templates/page-matrix-register.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}
		return $template;
	}
);
