<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add hero image to Customizer
add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_section(
			'scam_dev_hero',
			array(
				'title'    => __( 'Hero Section', 'scam-dev' ),
				'priority' => 30,
			)
		);

		$wp_customize->add_setting(
			'hero_svg_id',
			array(
				'default'           => '',
				'sanitize_callback' => 'absint',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'hero_svg_id',
				array(
					'label'       => __( 'Hero Illustration (SVG)', 'scam-dev' ),
					'description' => __( 'Upload an SVG illustration. Leave empty for default.', 'scam-dev' ),
					'section'     => 'scam_dev_hero',
					'mime_type'   => 'image',
				)
			)
		);
	}
);

function scam_dev_hero_svg_url() {
	$id = get_theme_mod( 'hero_svg_id', 0 );
	if ( $id ) {
		return wp_get_attachment_url( $id );
	}
	return '';
}
