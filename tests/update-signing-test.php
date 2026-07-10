<?php
/**
 * Runtime regression test for the Ed25519 update-manifest signer.
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( 1 );
}

if ( ! function_exists( 'sodium_crypto_sign_keypair' ) ) {
	fwrite( STDERR, "sodium is required\n" );
	exit( 1 );
}

$root = dirname( __DIR__ );
$temp = sys_get_temp_dir() . '/scam-dev-signing-' . bin2hex( random_bytes( 8 ) );

if ( ! mkdir( $temp, 0700, true ) && ! is_dir( $temp ) ) {
	fwrite( STDERR, "Could not create temp directory\n" );
	exit( 1 );
}

$payload_file = $temp . '/payload.json';
$key_file     = $temp . '/private.key';
$manifest_file = $temp . '/manifest.json';
$keypair       = sodium_crypto_sign_keypair();
$secret_key    = sodium_crypto_sign_secretkey( $keypair );
$public_key    = sodium_crypto_sign_publickey( $keypair );
$payload       = json_encode(
	array(
		'download_url' => 'https://scam-dev.ru/downloads/scam-dev-1.3.0.zip',
		'requires'     => '6.5',
		'requires_php' => '8.0',
		'sha256'       => str_repeat( 'a', 64 ),
		'version'      => '1.3.0',
	),
	JSON_UNESCAPED_SLASHES
) . "\n";

file_put_contents( $payload_file, $payload, LOCK_EX );
file_put_contents( $key_file, base64_encode( $secret_key ), LOCK_EX );

$command = escapeshellarg( PHP_BINARY )
	. ' '
	. escapeshellarg( $root . '/scripts/sign-update-manifest.php' )
	. ' '
	. escapeshellarg( $payload_file )
	. ' '
	. escapeshellarg( $key_file )
	. ' '
	. escapeshellarg( $manifest_file );

exec( $command, $output, $status );

if ( 0 !== $status || ! is_readable( $manifest_file ) ) {
	fwrite( STDERR, "Signer failed\n" );
	exit( 1 );
}

$manifest = json_decode( file_get_contents( $manifest_file ), true );
$decoded_payload = isset( $manifest['payload'] )
	? base64_decode( $manifest['payload'], true )
	: false;
$signature = isset( $manifest['signature'] )
	? base64_decode( $manifest['signature'], true )
	: false;

if ( $payload !== $decoded_payload
	|| false === $signature
	|| ! sodium_crypto_sign_verify_detached(
		$signature,
		$decoded_payload,
		$public_key
	)
) {
	fwrite( STDERR, "Signature verification failed\n" );
	exit( 1 );
}

@unlink( $manifest_file );
@unlink( $payload_file );
@unlink( $key_file );
@rmdir( $temp );

fwrite( STDOUT, "Update signing runtime test passed.\n" );
