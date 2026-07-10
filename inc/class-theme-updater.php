<?php
/**
 * Signed theme update client.
 *
 * The manifest format is:
 *
 * {
 *   "payload":   "<base64-encoded canonical JSON>",
 *   "signature": "<base64-encoded Ed25519 detached signature>"
 * }
 *
 * The public key must be configured outside the repository:
 *
 * define( 'SCAM_DEV_UPDATE_PUBLIC_KEY', '<base64 32-byte Ed25519 public key>' );
 *
 * @package Scam_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Signed update client for the Scam Dev theme.
 */
final class Scam_Dev_Theme_Updater {

	/**
	 * Theme directory slug.
	 *
	 * @var string
	 */
	private $theme_slug;

	/**
	 * Current theme version.
	 *
	 * @var string
	 */
	private $theme_version;

	/**
	 * Signed manifest URL.
	 *
	 * @var string
	 */
	private $manifest_url;

	/**
	 * Allowed package host.
	 *
	 * @var string
	 */
	private $allowed_host;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$theme = wp_get_theme( get_template() );

		$this->theme_slug    = $theme->get_template();
		$this->theme_version = (string) $theme->get( 'Version' );
		$this->manifest_url  = defined( 'SCAM_DEV_UPDATE_MANIFEST_URL' )
			? (string) SCAM_DEV_UPDATE_MANIFEST_URL
			: 'https://scam-dev.ru/theme-update.json';
		$this->allowed_host  = (string) wp_parse_url( $this->manifest_url, PHP_URL_HOST );

		add_filter(
			'pre_set_site_transient_update_themes',
			array( $this, 'check_update' )
		);
		add_filter(
			'upgrader_pre_download',
			array( $this, 'download_and_verify_package' ),
			10,
			4
		);
	}

	/**
	 * Add update information after verifying the signed manifest.
	 *
	 * @param stdClass $transient Theme update transient.
	 * @return stdClass
	 */
	public function check_update( $transient ) {
		if ( ! is_object( $transient ) || empty( $transient->checked ) ) {
			return $transient;
		}

		$data = $this->fetch_verified_manifest();

		if ( is_wp_error( $data ) ) {
			return $transient;
		}

		$version      = isset( $data['version'] ) ? (string) $data['version'] : '';
		$download_url = isset( $data['download_url'] ) ? esc_url_raw( $data['download_url'] ) : '';
		$sha256       = isset( $data['sha256'] ) ? strtolower( (string) $data['sha256'] ) : '';

		if ( ! preg_match( '/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/', $version ) ) {
			return $transient;
		}

		if ( ! $this->is_allowed_package_url( $download_url ) ) {
			return $transient;
		}

		if ( ! preg_match( '/^[a-f0-9]{64}$/', $sha256 ) ) {
			return $transient;
		}

		if ( version_compare( $this->theme_version, $version, '>=' ) ) {
			return $transient;
		}

		set_site_transient(
			$this->get_hash_transient_key( $download_url ),
			$sha256,
			12 * HOUR_IN_SECONDS
		);

		$transient->response[ $this->theme_slug ] = array(
			'theme'        => $this->theme_slug,
			'new_version'  => $version,
			'url'          => isset( $data['url'] ) ? esc_url_raw( $data['url'] ) : '',
			'package'      => $download_url,
			'requires'     => isset( $data['requires'] ) ? sanitize_text_field( $data['requires'] ) : '6.5',
			'requires_php' => isset( $data['requires_php'] ) ? sanitize_text_field( $data['requires_php'] ) : '8.0',
		);

		return $transient;
	}

	/**
	 * Download the theme package and verify its SHA-256 before WordPress unpacks it.
	 *
	 * @param false|string|WP_Error $reply      Previous filter value.
	 * @param string                $package    Package URL.
	 * @param WP_Upgrader           $upgrader   Upgrader instance.
	 * @param array                 $hook_extra Extra update context.
	 * @return false|string|WP_Error
	 */
	public function download_and_verify_package( $reply, $package, $upgrader, $hook_extra ) {
		unset( $upgrader );

		if ( empty( $hook_extra['theme'] ) || $this->theme_slug !== $hook_extra['theme'] ) {
			return $reply;
		}

		if ( ! $this->is_allowed_package_url( $package ) ) {
			return new WP_Error(
				'scam_dev_update_host',
				__( 'Обновление отклонено: недоверенный адрес пакета.', 'scam-dev' )
			);
		}

		$expected_hash = $this->get_expected_hash_for_package( $package );

		if ( is_wp_error( $expected_hash ) ) {
			return $expected_hash;
		}

		if ( is_string( $reply ) && is_readable( $reply ) ) {
			$temp_file = $reply;
		} elseif ( false === $reply ) {
			if ( ! function_exists( 'download_url' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			$temp_file = download_url( $package, 300 );

			if ( is_wp_error( $temp_file ) ) {
				return $temp_file;
			}
		} else {
			return $reply;
		}

		$actual_hash = hash_file( 'sha256', $temp_file );

		if ( ! is_string( $actual_hash )
			|| ! hash_equals( $expected_hash, $actual_hash )
		) {
			wp_delete_file( $temp_file );
			delete_site_transient( $this->get_hash_transient_key( $package ) );

			return new WP_Error(
				'scam_dev_update_hash_mismatch',
				__( 'Обновление отклонено: контрольная сумма пакета не совпадает.', 'scam-dev' )
			);
		}

		delete_site_transient( $this->get_hash_transient_key( $package ) );

		return $temp_file;
	}


	/**
	 * Return a verified package hash, refreshing the signed manifest if needed.
	 *
	 * @param string $package Package URL.
	 * @return string|WP_Error
	 */
	private function get_expected_hash_for_package( $package ) {
		$key             = $this->get_hash_transient_key( $package );
		$expected_hash   = get_site_transient( $key );
		$valid_hash      = is_string( $expected_hash )
			&& (bool) preg_match( '/^[a-f0-9]{64}$/', $expected_hash );

		if ( $valid_hash ) {
			return $expected_hash;
		}

		$data = $this->fetch_verified_manifest();

		if ( is_wp_error( $data ) ) {
			return new WP_Error(
				'scam_dev_update_manifest_refresh',
				__( 'Не удалось повторно проверить манифест обновления.', 'scam-dev' ),
				$data
			);
		}

		$manifest_package = isset( $data['download_url'] )
			? esc_url_raw( $data['download_url'] )
			: '';
		$manifest_hash = isset( $data['sha256'] )
			? strtolower( (string) $data['sha256'] )
			: '';

		if ( $package !== $manifest_package
			|| ! $this->is_allowed_package_url( $manifest_package )
			|| ! preg_match( '/^[a-f0-9]{64}$/', $manifest_hash )
		) {
			return new WP_Error(
				'scam_dev_update_package_not_in_manifest',
				__( 'Пакет не соответствует подписанному манифесту.', 'scam-dev' )
			);
		}

		set_site_transient( $key, $manifest_hash, 12 * HOUR_IN_SECONDS );

		return $manifest_hash;
	}

	/**
	 * Fetch and verify the signed update manifest.
	 *
	 * @return array|WP_Error
	 */
	private function fetch_verified_manifest() {
		if ( ! function_exists( 'sodium_crypto_sign_verify_detached' ) ) {
			return new WP_Error(
				'scam_dev_update_sodium_missing',
				__( 'Модуль sodium недоступен.', 'scam-dev' )
			);
		}

		if ( ! defined( 'SCAM_DEV_UPDATE_PUBLIC_KEY' ) ) {
			return new WP_Error(
				'scam_dev_update_key_missing',
				__( 'Публичный ключ обновлений не настроен.', 'scam-dev' )
			);
		}

		$manifest_host   = wp_parse_url( $this->manifest_url, PHP_URL_HOST );
		$manifest_scheme = wp_parse_url( $this->manifest_url, PHP_URL_SCHEME );

		if ( 'https' !== $manifest_scheme || $this->allowed_host !== $manifest_host ) {
			return new WP_Error(
				'scam_dev_update_manifest_url',
				__( 'Некорректный URL манифеста обновлений.', 'scam-dev' )
			);
		}

		$response = wp_safe_remote_get(
			$this->manifest_url,
			array(
				'timeout'             => 10,
				'redirection'         => 0,
				'limit_response_size' => 65536,
				'headers'             => array(
					'Accept' => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error(
				'scam_dev_update_manifest_status',
				__( 'Сервер обновлений вернул неожиданный статус.', 'scam-dev' )
			);
		}

		$manifest = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $manifest )
			|| empty( $manifest['payload'] )
			|| empty( $manifest['signature'] )
		) {
			return new WP_Error(
				'scam_dev_update_manifest_format',
				__( 'Некорректный формат манифеста обновлений.', 'scam-dev' )
			);
		}

		$payload    = base64_decode( (string) $manifest['payload'], true );
		$signature  = base64_decode( (string) $manifest['signature'], true );
		$public_key = base64_decode(
			(string) SCAM_DEV_UPDATE_PUBLIC_KEY,
			true
		);

		if ( false === $payload
			|| false === $signature
			|| false === $public_key
			|| SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES !== strlen( $public_key )
			|| SODIUM_CRYPTO_SIGN_BYTES !== strlen( $signature )
		) {
			return new WP_Error(
				'scam_dev_update_manifest_encoding',
				__( 'Некорректная кодировка подписи обновления.', 'scam-dev' )
			);
		}

		if ( ! sodium_crypto_sign_verify_detached( $signature, $payload, $public_key ) ) {
			return new WP_Error(
				'scam_dev_update_bad_signature',
				__( 'Подпись обновления не прошла проверку.', 'scam-dev' )
			);
		}

		$data = json_decode( $payload, true );

		if ( ! is_array( $data ) ) {
			return new WP_Error(
				'scam_dev_update_payload',
				__( 'Подписанные данные обновления повреждены.', 'scam-dev' )
			);
		}

		return $data;
	}

	/**
	 * Validate a package URL.
	 *
	 * @param string $url Package URL.
	 * @return bool
	 */
	private function is_allowed_package_url( $url ) {
		if ( ! wp_http_validate_url( $url ) ) {
			return false;
		}

		return 'https' === wp_parse_url( $url, PHP_URL_SCHEME )
			&& $this->allowed_host === wp_parse_url( $url, PHP_URL_HOST )
			&& null === wp_parse_url( $url, PHP_URL_USER )
			&& null === wp_parse_url( $url, PHP_URL_PASS )
			&& null === wp_parse_url( $url, PHP_URL_PORT );
	}

	/**
	 * Build a package-specific transient key.
	 *
	 * @param string $package Package URL.
	 * @return string
	 */
	private function get_hash_transient_key( $package ) {
		return 'scam_dev_update_' . hash( 'sha256', $package );
	}
}

new Scam_Dev_Theme_Updater();
