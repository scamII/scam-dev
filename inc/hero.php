<?php
/**
 * Hero media setting.
 *
 * @package Scam_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register hero media in the Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function scam_dev_register_hero_customizer( $wp_customize ) {
	$wp_customize->add_section(
		'scam_dev_hero',
		array(
			'title'    => esc_html__( 'Главный экран', 'scam-dev' ),
			'priority' => 30,
		)
	);

	$wp_customize->add_setting(
		'hero_media_id',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'hero_media_id',
			array(
				'label'       => esc_html__( 'Иллюстрация главного экрана', 'scam-dev' ),
				'description' => esc_html__( 'Безопасное изображение из медиатеки. Если поле пустое, используется встроенная иллюстрация.', 'scam-dev' ),
				'section'     => 'scam_dev_hero',
				'mime_type'   => 'image',
			)
		)
	);
}
add_action( 'customize_register', 'scam_dev_register_hero_customizer' );

/**
 * Return validated hero media data.
 *
 * Supports the legacy hero_svg_id setting for existing installations.
 *
 * @return array{url:string,alt:string}|false
 */
function scam_dev_get_hero_media() {
	$attachment_id = absint( get_theme_mod( 'hero_media_id', 0 ) );

	if ( ! $attachment_id ) {
		$attachment_id = absint( get_theme_mod( 'hero_svg_id', 0 ) );
	}

	if ( ! $attachment_id || ! wp_attachment_is_image( $attachment_id ) ) {
		return false;
	}

	$url = wp_get_attachment_image_url( $attachment_id, 'full' );

	if ( ! $url ) {
		return false;
	}

	return array(
		'url' => $url,
		'alt' => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
	);
}
