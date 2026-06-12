<?php

function enqueue_highlightjs() {
	wp_enqueue_style(
		'highlightjs-light',
		get_template_directory_uri() . '/assets/css/highlight.css',
		array(),
		filemtime( get_template_directory() . '/assets/css/highlight.css' ),
		'(prefers-color-scheme: light)'
	);

	wp_enqueue_style(
		'highlightjs-dark',
		get_template_directory_uri() . '/assets/css/highlight-dark.css',
		array(),
		filemtime( get_template_directory() . '/assets/css/highlight-dark.css' ),
		'(prefers-color-scheme: dark)'
	);

	wp_enqueue_script(
		'highlightjs',
		get_template_directory_uri() . '/assets/js/highlight.min.js',
		array(),
		filemtime( get_template_directory() . '/assets/js/highlight.min.js' ),
		true
	);

	wp_add_inline_script(
		'highlightjs',
		'hljs.configure({ignoreUnescapedHTML:true});hljs.highlightAll();'
	);
}
add_action( 'wp_enqueue_scripts', 'enqueue_highlightjs' );
