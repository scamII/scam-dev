<?php
/**
 * Runtime regression tests for the SVG sanitizer without loading WordPress.
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( 1 );
}

if ( ! class_exists( 'DOMDocument' ) ) {
	fwrite( STDERR, "DOM extension is required\n" );
	exit( 1 );
}

define( 'ABSPATH', __DIR__ . '/' );

class WP_Error {
	private $code;
	private $message;

	public function __construct( $code = '', $message = '' ) {
		$this->code    = $code;
		$this->message = $message;
	}

	public function get_error_code() {
		return $this->code;
	}

	public function get_error_message() {
		return $this->message;
	}
}

function __( $text ) {
	return $text;
}

function add_filter() {
	return true;
}

function current_user_can() {
	return true;
}

function is_wp_error( $value ) {
	return $value instanceof WP_Error;
}

require dirname( __DIR__ ) . '/plugins/scam-dev-svg/scam-dev-svg.php';

function assert_svg( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, $message . "\n" );
		exit( 1 );
	}
}

$valid = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M1 1h10v10z" fill="#000"/></svg>';
$valid_result = scam_dev_svg_sanitize( $valid );
assert_svg( ! is_wp_error( $valid_result ), 'Path-only SVG was rejected.' );
assert_svg( false !== strpos( $valid_result, '<path' ), 'Path was removed.' );

$malicious = '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)">'
	. '<script>alert(1)</script>'
	. '<path d="M1 1h2v2z" fill="url(https://evil.example/a)"/>'
	. '</svg>';
$malicious_result = scam_dev_svg_sanitize( $malicious );
assert_svg( ! is_wp_error( $malicious_result ), 'Sanitizable SVG was rejected.' );
assert_svg( false === stripos( $malicious_result, '<script' ), 'Script survived.' );
assert_svg( false === stripos( $malicious_result, 'onload' ), 'Event handler survived.' );
assert_svg( false === stripos( $malicious_result, 'https:' ), 'External reference survived.' );

$doctype = '<!DOCTYPE svg [<!ENTITY xxe SYSTEM "file:///etc/passwd">]>'
	. '<svg xmlns="http://www.w3.org/2000/svg"><text>&xxe;</text></svg>';
$doctype_result = scam_dev_svg_sanitize( $doctype );
assert_svg( is_wp_error( $doctype_result ), 'DOCTYPE payload was not rejected.' );


$foreign_namespace = '<svg xmlns="http://www.w3.org/2000/svg" '
	. 'xmlns:evil="https://evil.example/ns">'
	. '<path d="M1 1h2v2z"/><evil:path d="M9 9h2v2z"/>'
	. '</svg>';
$foreign_result = scam_dev_svg_sanitize( $foreign_namespace );
assert_svg( ! is_wp_error( $foreign_result ), 'Foreign child namespace caused failure.' );
assert_svg( false === stripos( $foreign_result, 'evil:' ), 'Foreign namespace survived.' );

$bad_root = '<svg xmlns="https://evil.example/ns"><path d="M1 1h2v2z"/></svg>';
assert_svg(
	is_wp_error( scam_dev_svg_sanitize( $bad_root ) ),
	'Foreign root namespace was not rejected.'
);

$complex = '<svg xmlns="http://www.w3.org/2000/svg">'
	. str_repeat( '<path d="M1 1h2v2z"/>', SCAM_DEV_SVG_MAX_NODES + 1 )
	. '</svg>';
assert_svg(
	is_wp_error( scam_dev_svg_sanitize( $complex ) ),
	'Excessive SVG node count was not rejected.'
);

fwrite( STDOUT, "SVG sanitizer runtime tests passed.\n" );
