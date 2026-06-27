<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Allow SVG uploads for administrators only
add_filter(
	'upload_mimes',
	function ( $mimes ) {
		if ( current_user_can( 'upload_files' ) && current_user_can( 'unfiltered_html' ) ) {
			$mimes['svg'] = 'image/svg+xml';
		}
		return $mimes;
	}
);

// Sanitize SVG on upload
add_filter(
	'wp_handle_upload_prefilter',
	function ( $file ) {
		if ( 'image/svg+xml' !== $file['type'] ) {
			return $file;
		}

		$svg = scam_dev_safe_file_read( $file['tmp_name'] );
		if ( false === $svg ) {
			$file['error'] = __( 'Failed to read SVG file.', 'scam-dev' );
			return $file;
		}

		$svg     = scam_dev_sanitize_svg( $svg );
		$cleaned = trim( wp_strip_all_tags( $svg, true ) );
		if ( empty( $cleaned ) ) {
			$file['error'] = __( 'SVG file appears to be empty or invalid.', 'scam-dev' );
			return $file;
		}

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
		$wp_filesystem->put_contents( $file['tmp_name'], $svg, FS_CHMOD_FILE );
		return $file;
	}
);

// Fix SVG display in media library
add_filter(
	'wp_prepare_attachment_for_js',
	function ( $response, $attachment ) {
		if ( 'image/svg+xml' === $response['mime'] ) {
			$response['icon']  = $response['url'];
			$response['image'] = array(
				'src' => $response['url'],
			);
		}
		return $response;
	},
	10,
	2
);

// Sanitize SVG markup - allow only safe elements and attributes

function scam_dev_safe_file_read( $path ) {
	global $wp_filesystem;
	if ( empty( $wp_filesystem ) ) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();
	}
	return $wp_filesystem->get_contents( $path );
}

function scam_dev_sanitize_svg( $svg ) {
	$allowed = array(
		'svg'            => array(
			'xmlns'       => true,
			'viewbox'     => true,
			'width'       => true,
			'height'      => true,
			'fill'        => true,
			'stroke'      => true,
			'class'       => true,
			'id'          => true,
			'role'        => true,
			'aria-hidden' => true,
			'focusable'   => true,
			'version'     => true,
			'x'           => true,
			'y'           => true,
			'style'       => true,
		),
		'defs'           => true,
		'style'          => array( 'type' => true ),
		'g'              => array(
			'id'        => true,
			'class'     => true,
			'transform' => true,
			'fill'      => true,
			'stroke'    => true,
			'opacity'   => true,
			'style'     => true,
		),
		'path'           => array(
			'd'                => true,
			'fill'             => true,
			'stroke'           => true,
			'stroke-width'     => true,
			'stroke-dasharray' => true,
			'stroke-linecap'   => true,
			'stroke-linejoin'  => true,
			'opacity'          => true,
			'class'            => true,
			'style'            => true,
		),
		'rect'           => array(
			'x'                => true,
			'y'                => true,
			'width'            => true,
			'height'           => true,
			'rx'               => true,
			'ry'               => true,
			'fill'             => true,
			'fill-opacity'     => true,
			'stroke'           => true,
			'stroke-width'     => true,
			'stroke-dasharray' => true,
			'opacity'          => true,
			'class'            => true,
			'style'            => true,
		),
		'circle'         => array(
			'cx'           => true,
			'cy'           => true,
			'r'            => true,
			'fill'         => true,
			'fill-opacity' => true,
			'stroke'       => true,
			'opacity'      => true,
			'class'        => true,
			'style'        => true,
		),
		'ellipse'        => array(
			'cx'      => true,
			'cy'      => true,
			'rx'      => true,
			'ry'      => true,
			'fill'    => true,
			'stroke'  => true,
			'opacity' => true,
			'class'   => true,
			'style'   => true,
		),
		'line'           => array(
			'x1'           => true,
			'y1'           => true,
			'x2'           => true,
			'y2'           => true,
			'stroke'       => true,
			'stroke-width' => true,
			'opacity'      => true,
			'class'        => true,
			'style'        => true,
		),
		'polygon'        => array(
			'points'       => true,
			'fill'         => true,
			'stroke'       => true,
			'stroke-width' => true,
			'opacity'      => true,
			'class'        => true,
			'style'        => true,
		),
		'linearGradient' => array(
			'id' => true,
			'x1' => true,
			'y1' => true,
			'x2' => true,
			'y2' => true,
		),
		'stop'           => array(
			'offset'       => true,
			'stop-color'   => true,
			'stop-opacity' => true,
		),
		'filter'         => array(
			'id' => true,
		),
		'feGaussianBlur' => array(
			'stdDeviation' => true,
			'result'       => true,
		),
		'feMerge'        => true,
		'feMergeNode'    => array( 'in' => true ),
		'text'           => array(
			'x'           => true,
			'y'           => true,
			'font-size'   => true,
			'font-family' => true,
			'fill'        => true,
			'text-anchor' => true,
			'class'       => true,
		),
	);

	return wp_kses( $svg, $allowed );
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
