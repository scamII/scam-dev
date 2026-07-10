<?php
/**
 * Theme Customizer settings.
 *
 * @package Scam_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Customizer settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function scam_dev_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'scam_dev_footer',
		array(
			'title'    => esc_html__( 'Подвал и контакты', 'scam-dev' ),
			'priority' => 160,
		)
	);

	$wp_customize->add_setting(
		'footer_text',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'footer_text',
		array(
			'label'   => esc_html__( 'Текст в подвале', 'scam-dev' ),
			'section' => 'scam_dev_footer',
			'type'    => 'text',
		)
	);

	$url_settings = array(
		'github_url' => array(
			'label'   => __( 'GitHub URL', 'scam-dev' ),
			'default' => 'https://github.com/scamII/scam-dev',
		),
		'matrix_url' => array(
			'label'   => __( 'Matrix URL', 'scam-dev' ),
			'default' => 'https://chat.scam-dev.ru',
		),
	);

	foreach ( $url_settings as $setting_id => $setting ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => $setting['default'],
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$setting_id,
			array(
				'label'   => esc_html( $setting['label'] ),
				'section' => 'scam_dev_footer',
				'type'    => 'url',
			)
		);
	}

	$wp_customize->add_setting(
		'contact_email',
		array(
			'default'           => get_option( 'admin_email' ),
			'sanitize_callback' => 'sanitize_email',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'contact_email',
		array(
			'label'   => esc_html__( 'Контактный email', 'scam-dev' ),
			'section' => 'scam_dev_footer',
			'type'    => 'email',
		)
	);
}
add_action( 'customize_register', 'scam_dev_customize_register' );
