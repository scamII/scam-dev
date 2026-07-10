<?php
/**
 * Frontend assets.
 *
 * @package Scam_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return a file modification time or the theme version.
 *
 * @param string $path Absolute file path.
 * @return string|int
 */
function scam_dev_asset_version( $path ) {
	return is_readable( $path ) ? filemtime( $path ) : SCAM_DEV_VERSION;
}

/**
 * Enqueue theme styles and scripts.
 */
function scam_dev_scripts() {
	$theme_dir = get_template_directory();
	$theme_uri = get_template_directory_uri();

	$init_path = $theme_dir . '/assets/js/theme-init.js';
	if ( is_readable( $init_path ) ) {
		wp_enqueue_script(
			'scam-dev-theme-init',
			$theme_uri . '/assets/js/theme-init.js',
			array(),
			scam_dev_asset_version( $init_path ),
			false
		);
	}

	$css_path = $theme_dir . '/assets/css/style.css';
	if ( is_readable( $css_path ) ) {
		wp_enqueue_style(
			'scam-dev-style',
			$theme_uri . '/assets/css/style.css',
			array(),
			scam_dev_asset_version( $css_path )
		);
	}

	$js_path    = $theme_dir . '/assets/js/app.js';
	$asset_path = $theme_dir . '/assets/js/app.asset.php';

	if ( is_readable( $js_path ) ) {
		$asset = array(
			'dependencies' => array( 'react', 'react-dom', 'react-jsx-runtime' ),
			'version'      => scam_dev_asset_version( $js_path ),
		);

		if ( is_readable( $asset_path ) ) {
			$generated_asset = require $asset_path;
			if ( is_array( $generated_asset ) ) {
				$asset = wp_parse_args( $generated_asset, $asset );
			}
		}

		wp_enqueue_script(
			'scam-dev-app',
			$theme_uri . '/assets/js/app.js',
			(array) $asset['dependencies'],
			$asset['version'],
			true
		);
		wp_script_add_data( 'scam-dev-app', 'strategy', 'defer' );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'scam_dev_scripts' );
