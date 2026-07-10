<?php
/**
 * Syntax highlighting styles.
 *
 * JavaScript is bundled from the locked npm dependency.
 *
 * @package Scam_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine whether the current singular post contains code.
 *
 * @return bool
 */
function scam_dev_has_code_blocks() {
	if ( ! is_singular() ) {
		return false;
	}

	$post = get_post();

	if ( ! $post ) {
		return false;
	}

	return has_block( 'core/code', $post )
		|| false !== stripos( $post->post_content, '<pre' )
		|| false !== stripos( $post->post_content, '<code' );
}

/**
 * Enqueue light and dark highlight.js themes only where needed.
 */
function scam_dev_enqueue_highlight_styles() {
	if ( ! scam_dev_has_code_blocks() ) {
		return;
	}

	$theme_dir = get_template_directory();
	$theme_uri = get_template_directory_uri();
	$styles    = array(
		'highlightjs-light' => 'highlight.css',
		'highlightjs-dark'  => 'highlight-dark.css',
	);

	foreach ( $styles as $handle => $file ) {
		$path = $theme_dir . '/assets/css/' . $file;

		if ( ! is_readable( $path ) ) {
			continue;
		}

		wp_enqueue_style(
			$handle,
			$theme_uri . '/assets/css/' . $file,
			array(),
			scam_dev_asset_version( $path )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'scam_dev_enqueue_highlight_styles', 20 );
