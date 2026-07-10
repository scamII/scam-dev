<?php
/**
 * Plugin Name: Scam Dev SVG Support
 * Description: Fail-closed SVG upload validation and sanitization for administrators.
 * Version: 1.3.0
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Author: Scam Dev
 * License: GPL v2 or later
 * Text Domain: scam-dev-svg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SCAM_DEV_SVG_VERSION', '1.3.0' );
define( 'SCAM_DEV_SVG_MAX_BYTES', 1048576 );
define( 'SCAM_DEV_SVG_MAX_NODES', 5000 );

/**
 * Allow SVG uploads only for trusted administrators.
 *
 * @param array $mimes Allowed MIME types.
 * @return array
 */
function scam_dev_svg_upload_mimes( $mimes ) {
	if ( current_user_can( 'upload_files' )
		&& current_user_can( 'unfiltered_html' )
	) {
		$mimes['svg'] = 'image/svg+xml';
	}

	return $mimes;
}
add_filter( 'upload_mimes', 'scam_dev_svg_upload_mimes' );

/**
 * Sanitize SVG content before WordPress moves the temporary file.
 *
 * @param array $file Upload data.
 * @return array
 */
function scam_dev_svg_upload_prefilter( $file ) {
	$name      = isset( $file['name'] ) ? (string) $file['name'] : '';
	$extension = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );

	if ( 'svg' !== $extension ) {
		return $file;
	}

	if ( ! current_user_can( 'upload_files' )
		|| ! current_user_can( 'unfiltered_html' )
	) {
		$file['error'] = __(
			'У вас нет прав на загрузку SVG.',
			'scam-dev-svg'
		);
		return $file;
	}

	$temp_name = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';
	$file_size = isset( $file['size'] ) ? (int) $file['size'] : 0;

	if ( ! $temp_name
		|| ! is_readable( $temp_name )
		|| $file_size <= 0
		|| $file_size > SCAM_DEV_SVG_MAX_BYTES
	) {
		$file['error'] = __(
			'SVG отсутствует, недоступен или превышает 1 МБ.',
			'scam-dev-svg'
		);
		return $file;
	}

	if ( ! class_exists( 'finfo' ) ) {
		$file['error'] = __( 'Fileinfo extension недоступно; SVG отклонён.', 'scam-dev-svg' );
		return $file;
	}

	$finfo         = new finfo( FILEINFO_MIME_TYPE );
	$mime          = $finfo->file( $temp_name );
	$allowed_mimes = array(
		'image/svg+xml',
		'application/xml',
		'text/xml',
		'text/plain',
	);

	if ( ! in_array( $mime, $allowed_mimes, true ) ) {
		$file['error'] = __(
			'Содержимое файла не похоже на SVG.',
			'scam-dev-svg'
		);
		return $file;
	}

	$svg = file_get_contents( $temp_name ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	if ( false === $svg ) {
		$file['error'] = __(
			'Не удалось прочитать SVG.',
			'scam-dev-svg'
		);
		return $file;
	}

	$sanitized = scam_dev_svg_sanitize( $svg );

	if ( is_wp_error( $sanitized ) ) {
		$file['error'] = $sanitized->get_error_message();
		return $file;
	}

	$written = file_put_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		$temp_name,
		$sanitized,
		LOCK_EX
	);

	if ( false === $written || $written !== strlen( $sanitized ) ) {
		$file['error'] = __(
			'Не удалось сохранить очищенный SVG.',
			'scam-dev-svg'
		);
		return $file;
	}

	$file['type'] = 'image/svg+xml';
	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'scam_dev_svg_upload_prefilter' );

/**
 * Parse and sanitize an SVG document.
 *
 * @param string $svg Raw SVG.
 * @return string|WP_Error
 */
function scam_dev_svg_sanitize( $svg ) {
	if ( ! class_exists( 'DOMDocument' ) ) {
		return new WP_Error(
			'svg_dom_missing',
			__( 'DOM extension недоступно; SVG отклонён.', 'scam-dev-svg' )
		);
	}

	if ( false !== stripos( $svg, '<!DOCTYPE' )
		|| false !== stripos( $svg, '<!ENTITY' )
		|| false !== stripos( $svg, '<?xml-stylesheet' )
	) {
		return new WP_Error(
			'svg_forbidden_declaration',
			__( 'SVG содержит запрещённые XML-декларации.', 'scam-dev-svg' )
		);
	}

	$previous = libxml_use_internal_errors( true );
	$dom      = new DOMDocument();
	$loaded   = $dom->loadXML(
		$svg,
		LIBXML_NONET | LIBXML_NOBLANKS | LIBXML_NOERROR | LIBXML_NOWARNING
	);
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	if ( ! $loaded
		|| ! $dom->documentElement
		|| 'svg' !== strtolower( $dom->documentElement->localName )
	) {
		return new WP_Error(
			'svg_invalid_xml',
			__( 'SVG имеет некорректную XML-структуру.', 'scam-dev-svg' )
		);
	}

	$root_namespace = (string) $dom->documentElement->namespaceURI;

	if ( '' !== $root_namespace
		&& 'http://www.w3.org/2000/svg' !== $root_namespace
	) {
		return new WP_Error(
			'svg_invalid_namespace',
			__( 'SVG использует недоверенное пространство имён.', 'scam-dev-svg' )
		);
	}

	$allowed_elements = array(
		'svg',
		'g',
		'defs',
		'title',
		'desc',
		'path',
		'rect',
		'circle',
		'ellipse',
		'line',
		'polyline',
		'polygon',
		'text',
		'tspan',
		'lineargradient',
		'radialgradient',
		'stop',
		'clippath',
		'mask',
		'filter',
		'fegaussianblur',
		'femerge',
		'femergenode',
	);

	$global_attributes = array(
		'id',
		'class',
		'role',
		'aria-hidden',
		'aria-label',
		'focusable',
		'viewbox',
		'width',
		'height',
		'x',
		'y',
		'x1',
		'y1',
		'x2',
		'y2',
		'cx',
		'cy',
		'r',
		'rx',
		'ry',
		'd',
		'points',
		'transform',
		'fill',
		'fill-opacity',
		'fill-rule',
		'stroke',
		'stroke-width',
		'stroke-opacity',
		'stroke-dasharray',
		'stroke-dashoffset',
		'stroke-linecap',
		'stroke-linejoin',
		'opacity',
		'offset',
		'stop-color',
		'stop-opacity',
		'gradientunits',
		'gradienttransform',
		'clip-path',
		'mask',
		'filter',
		'stddeviation',
		'result',
		'in',
		'in2',
		'font-size',
		'font-family',
		'font-weight',
		'text-anchor',
		'dominant-baseline',
		'preserveaspectratio',
		'version',
		'xmlns',
	);

	$graphics_count = 0;
	$node_count     = 0;
	$limit_exceeded = false;
	scam_dev_svg_clean_node(
		$dom->documentElement,
		$allowed_elements,
		$global_attributes,
		$graphics_count,
		$node_count,
		$limit_exceeded,
		0
	);

	if ( $limit_exceeded ) {
		return new WP_Error(
			'svg_too_complex',
			__( 'SVG превышает допустимую сложность.', 'scam-dev-svg' )
		);
	}

	if ( $graphics_count < 1 ) {
		return new WP_Error(
			'svg_no_graphics',
			__( 'SVG не содержит разрешённых графических элементов.', 'scam-dev-svg' )
		);
	}

	$output = $dom->saveXML( $dom->documentElement );

	if ( ! is_string( $output ) || '' === trim( $output ) ) {
		return new WP_Error(
			'svg_serialization',
			__( 'Не удалось сериализовать очищенный SVG.', 'scam-dev-svg' )
		);
	}

	return $output;
}

/**
 * Recursively clean one SVG node.
 *
 * @param DOMNode $node Current node.
 * @param string[] $allowed_elements Allowed element names.
 * @param string[] $allowed_attributes Allowed attribute names.
 * @param int  $graphics_count Number of graphics.
 * @param int  $node_count Number of parsed elements.
 * @param bool $limit_exceeded Whether a safety limit was exceeded.
 * @param int  $depth Current depth.
 */
function scam_dev_svg_clean_node(
	$node,
	$allowed_elements,
	$allowed_attributes,
	&$graphics_count,
	&$node_count,
	&$limit_exceeded,
	$depth
) {
	if ( XML_ELEMENT_NODE === $node->nodeType ) {
		++$node_count;
	}

	if ( $depth > 64 || $node_count > SCAM_DEV_SVG_MAX_NODES ) {
		$limit_exceeded = true;
		while ( $node->firstChild ) {
			$node->removeChild( $node->firstChild );
		}
		return;
	}

	if ( XML_ELEMENT_NODE === $node->nodeType ) {
		scam_dev_svg_clean_attributes( $node, $allowed_attributes );
	}

	for ( $child = $node->firstChild; $child; ) {
		$next = $child->nextSibling;

		if ( XML_ELEMENT_NODE === $child->nodeType ) {
			$name      = strtolower( $child->localName );
			$namespace = (string) $child->namespaceURI;

			if ( ( '' !== $namespace
					&& 'http://www.w3.org/2000/svg' !== $namespace )
				|| ! in_array( $name, $allowed_elements, true )
			) {
				$node->removeChild( $child );
				$child = $next;
				continue;
			}

			if ( in_array(
				$name,
				array(
					'path',
					'rect',
					'circle',
					'ellipse',
					'line',
					'polyline',
					'polygon',
					'text',
				),
				true
			) ) {
				++$graphics_count;
			}

			scam_dev_svg_clean_node(
				$child,
				$allowed_elements,
				$allowed_attributes,
				$graphics_count,
				$node_count,
				$limit_exceeded,
				$depth + 1
			);

			if ( $limit_exceeded ) {
				return;
			}
		} elseif ( XML_TEXT_NODE === $child->nodeType ) {
			if ( 'text' !== strtolower( $node->localName )
				&& 'tspan' !== strtolower( $node->localName )
				&& 'title' !== strtolower( $node->localName )
				&& 'desc' !== strtolower( $node->localName )
			) {
				$node->removeChild( $child );
			}
		} else {
			$node->removeChild( $child );
		}

		$child = $next;
	}
}

/**
 * Remove forbidden attributes from one SVG element.
 *
 * @param DOMElement $element SVG element.
 * @param string[]   $allowed_attributes Allowed names.
 */
function scam_dev_svg_clean_attributes( $element, $allowed_attributes ) {
	if ( ! $element->hasAttributes() ) {
		return;
	}

	$remove = array();

	foreach ( $element->attributes as $attribute ) {
		$attribute_name = strtolower( $attribute->nodeName );
		$value          = trim( $attribute->nodeValue );

		if ( str_starts_with( $attribute_name, 'on' )
			|| ! in_array( $attribute_name, $allowed_attributes, true )
			|| scam_dev_svg_attribute_is_unsafe( $attribute_name, $value )
		) {
			$remove[] = $attribute->nodeName;
		}
	}

	foreach ( $remove as $attribute_name ) {
		$element->removeAttribute( $attribute_name );
	}
}

/**
 * Detect unsafe CSS/URL-like SVG attribute values.
 *
 * @param string $attribute_name Attribute name.
 * @param string $value Attribute value.
 * @return bool
 */
function scam_dev_svg_attribute_is_unsafe( $attribute_name, $value ) {
	$normalized = strtolower(
		(string) preg_replace( '/[\x00-\x20\x7f]+/', '', $value )
	);

	if ( 'xmlns' === $attribute_name ) {
		return 'http://www.w3.org/2000/svg' !== $normalized;
	}

	if ( in_array(
		$attribute_name,
		array( 'fill', 'stroke', 'clip-path', 'mask', 'filter' ),
		true
	) && preg_match( '/^url\(#[a-z0-9_.:-]+\)$/i', $normalized )
	) {
		return false;
	}

	$forbidden = array(
		'javascript:',
		'vbscript:',
		'data:',
		'http:',
		'https:',
		'file:',
		'url(',
		'expression(',
		'@import',
	);

	foreach ( $forbidden as $needle ) {
		if ( false !== strpos( $normalized, $needle ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Improve SVG media-library preview.
 *
 * @param array   $response Attachment response.
 * @param WP_Post $attachment Attachment.
 * @return array
 */
function scam_dev_svg_prepare_attachment( $response, $attachment ) {
	if ( 'image/svg+xml' === get_post_mime_type( $attachment ) ) {
		$response['icon']  = $response['url'];
		$response['image'] = array(
			'src' => $response['url'],
		);
	}

	return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'scam_dev_svg_prepare_attachment', 10, 2 );
