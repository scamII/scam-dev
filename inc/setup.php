<?php
/**
 * Theme setup.
 *
 * @package Scam_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configure theme supports and menus.
 */
function scam_dev_setup() {
	load_theme_textdomain( 'scam-dev', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );

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

	register_nav_menus(
		array(
			'primary' => esc_html__( 'Основное меню', 'scam-dev' ),
			'footer'  => esc_html__( 'Меню в подвале', 'scam-dev' ),
		)
	);
}
add_action( 'after_setup_theme', 'scam_dev_setup' );

/**
 * Set content width.
 */
function scam_dev_content_width() {
	$GLOBALS['content_width'] = (int) apply_filters(
		'scam_dev_content_width',
		1200
	);
}
add_action( 'after_setup_theme', 'scam_dev_content_width', 0 );

/**
 * Register widget areas.
 */
function scam_dev_widgets_init() {
	$sidebars = array(
		array(
			'name'        => __( 'Боковая панель', 'scam-dev' ),
			'id'          => 'sidebar-1',
			'description' => __( 'Основная область виджетов.', 'scam-dev' ),
		),
		array(
			'name'        => __( 'Левая боковая панель', 'scam-dev' ),
			'id'          => 'sidebar-left',
			'description' => __( 'Левая область виджетов.', 'scam-dev' ),
		),
		array(
			'name'        => __( 'Подвал', 'scam-dev' ),
			'id'          => 'footer-1',
			'description' => __( 'Область виджетов в подвале.', 'scam-dev' ),
		),
	);

	foreach ( $sidebars as $sidebar ) {
		register_sidebar(
			array(
				'name'          => esc_html( $sidebar['name'] ),
				'id'            => $sidebar['id'],
				'description'   => esc_html( $sidebar['description'] ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);
	}
}
add_action( 'widgets_init', 'scam_dev_widgets_init' );
