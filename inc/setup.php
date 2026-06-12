<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function scam_dev_setup() {
	load_theme_textdomain( 'scam-dev', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 100,
			'width'       => 400,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support(
		'custom-background',
		apply_filters(
			'scam_dev_custom_background_args',
			array(
				'default-color' => 'f9fafb',
				'default-image' => '',
			)
		)
	);
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );

	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary Menu', 'scam-dev' ),
			'footer'  => esc_html__( 'Footer Menu', 'scam-dev' ),
		)
	);
}
add_action( 'after_setup_theme', 'scam_dev_setup' );

function scam_dev_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'scam_dev_content_width', 1200 );
}
add_action( 'after_setup_theme', 'scam_dev_content_width', 0 );

function scam_dev_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'scam-dev' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'scam-dev' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Left Sidebar', 'scam-dev' ),
			'id'            => 'sidebar-left',
			'description'   => esc_html__( 'Left sidebar widgets.', 'scam-dev' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer', 'scam-dev' ),
			'id'            => 'footer-1',
			'description'   => esc_html__( 'Add footer widgets here.', 'scam-dev' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title text-md font-semibold mb-3">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'scam_dev_widgets_init' );
