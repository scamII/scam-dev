<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function scam_dev_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	$wp_customize->add_section(
		'scam_dev_footer',
		array(
			'title'    => esc_html__( 'Footer Settings', 'scam-dev' ),
			'priority' => 160,
		)
	);

	$wp_customize->add_setting(
		'footer_text',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		)
	);

	$wp_customize->add_control(
		'footer_text',
		array(
			'label'   => esc_html__( 'Footer Text', 'scam-dev' ),
			'section' => 'scam_dev_footer',
			'type'    => 'text',
		)
	);
}
add_action( 'customize_register', 'scam_dev_customize_register' );
