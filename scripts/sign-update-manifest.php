<?php
/**
 * Sign a canonical update payload with an external Ed25519 secret key.
 *
 * Usage:
 * php scripts/sign-update-manifest.php payload.json private-key-file manifest.json
 */

if ( PHP_SAPI !== 'cli' ) {
	fwrite( STDERR, "CLI only.\n" );
	exit( 1 );
}

if ( 4 !== $argc ) {
	fwrite(
		STDERR,
		"Usage: php scripts/sign-update-manifest.php payload.json private-key-file manifest.json\n"
	);
	exit( 1 );
}

if ( ! function_exists( 'sodium_crypto_sign_detached' ) ) {
	fwrite( STDERR, "The sodium extension is required.\n" );
	exit( 1 );
}

[ , $payload_file, $private_key_file, $output_file ] = $argv;

$payload = file_get_contents( $payload_file );
$key_raw = trim( (string) file_get_contents( $private_key_file ) );

if ( false === $payload || '' === $payload || '' === $key_raw ) {
	fwrite( STDERR, "Payload or private key is unavailable.\n" );
	exit( 1 );
}

$private_key = base64_decode( $key_raw, true );

if ( false === $private_key ) {
	$private_key = $key_raw;
}

if ( SODIUM_CRYPTO_SIGN_SECRETKEYBYTES !== strlen( $private_key ) ) {
	fwrite(
		STDERR,
		"Expected an Ed25519 secret key containing "
		. SODIUM_CRYPTO_SIGN_SECRETKEYBYTES
		. " bytes (raw or base64).\n"
	);
	exit( 1 );
}

$signature = sodium_crypto_sign_detached( $payload, $private_key );
$manifest  = array(
	'payload'   => base64_encode( $payload ),
	'signature' => base64_encode( $signature ),
);

$encoded = json_encode(
	$manifest,
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
);

if ( false === $encoded
	|| false === file_put_contents( $output_file, $encoded . PHP_EOL, LOCK_EX )
) {
	fwrite( STDERR, "Could not write manifest.\n" );
	exit( 1 );
}

fwrite( STDOUT, "Signed manifest: {$output_file}\n" );
