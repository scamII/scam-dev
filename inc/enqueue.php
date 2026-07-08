<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function scam_dev_scripts() {
	$css_path = get_template_directory() . '/assets/css/style.css';
	$js_path  = get_template_directory() . '/assets/js/app.js';
	$css_ver  = file_exists( $css_path ) ? filemtime( $css_path ) : SCAM_DEV_VERSION;
	$js_ver   = file_exists( $js_path ) ? filemtime( $js_path ) : SCAM_DEV_VERSION;

	wp_enqueue_style(
		'scam-dev-style',
		get_template_directory_uri() . '/assets/css/style.css',
		array(),
		$css_ver
	);

	wp_enqueue_script(
		'scam-dev-app',
		get_template_directory_uri() . '/assets/js/app.js',
		array( 'react', 'react-dom', 'react-jsx-runtime' ),
		$js_ver,
		true
	);

	wp_add_inline_script(
		'scam-dev-app',
		'window.scamDev = window.scamDev || {}; window.scamDev.restUrl = ' . wp_json_encode( rest_url() ) . ';',
		'before'
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'scam_dev_scripts' );
