<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Scam_Dev_Theme_Updater {

	private $theme_slug;
	private $theme_version;
	private $update_url;
	private $allowed_host = 'scam-dev.ru';

	public function __construct() {
		$theme               = wp_get_theme();
		$this->theme_slug    = $theme->get_template();
		$this->theme_version = $theme->get( 'Version' );
		$this->update_url    = 'https://scam-dev.ru/theme-update.json';

		add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_update' ) );
		add_filter( 'auto_update_theme', array( $this, 'auto_update' ), 10, 2 );
		add_filter( 'upgrader_pre_install', array( $this, 'verify_package_hash' ), 10, 2 );
	}

	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$response = wp_remote_get( $this->update_url, array( 'timeout' => 10 ) );
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return $transient;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $data ) || empty( $data['version'] ) || empty( $data['download_url'] ) || empty( $data['sha256'] ) ) {
			return $transient;
		}

		$version      = preg_replace( '/[^0-9.]/', '', $data['version'] );
		$download_url = esc_url_raw( $data['download_url'] );

		if ( ! $version ) {
			return $transient;
		}

		$host = wp_parse_url( $download_url, PHP_URL_HOST );
		if ( $this->allowed_host !== $host || 'https' !== wp_parse_url( $download_url, PHP_URL_SCHEME ) ) {
			return $transient;
		}

		$expected_hash = preg_replace( '/[^a-f0-9]/', '', strtolower( $data['sha256'] ) );
		if ( 64 !== strlen( $expected_hash ) ) {
			return $transient;
		}

		if ( version_compare( $this->theme_version, $version, '<' ) ) {
			set_transient( 'scam_dev_updater_sha256', $expected_hash, 12 * HOUR_IN_SECONDS );

			$transient->response[ $this->theme_slug ] = array(
				'theme'        => $this->theme_slug,
				'new_version'  => $version,
				'url'          => isset( $data['url'] ) ? esc_url_raw( $data['url'] ) : '',
				'package'      => $download_url,
				'requires'     => isset( $data['requires'] ) ? preg_replace( '/[^0-9.]/', '', $data['requires'] ) : '6.5',
				'requires_php' => isset( $data['requires_php'] ) ? preg_replace( '/[^0-9.]/', '', $data['requires_php'] ) : '8.0',
			);
		}

		return $transient;
	}

	public function verify_package_hash( $return, $hook_extra ) {
		if ( empty( $hook_extra['theme'] ) || $hook_extra['theme'] !== $this->theme_slug ) {
			return $return;
		}

		$expected_hash = get_transient( 'scam_dev_updater_sha256' );
		if ( ! $expected_hash ) {
			return new \WP_Error( 'missing_hash', 'Обновление отклонено: отсутствует контрольная сумма.' );
		}

		$temp_files = isset( $hook_extra['temp_files'] ) ? $hook_extra['temp_files'] : array();
		$package    = isset( $temp_files['package'] ) ? $temp_files['package'] : '';

		if ( ! $package || ! file_exists( $package ) ) {
			return new \WP_Error( 'package_not_found', 'Обновление отклонено: файл пакета не найден.' );
		}

		$actual_hash = hash_file( 'sha256', $package );
		if ( $actual_hash !== $expected_hash ) {
			@unlink( $package );
			delete_transient( 'scam_dev_updater_sha256' );
			return new \WP_Error( 'hash_mismatch', 'Обновление отклонено: контрольная сумма не совпадает.' );
		}

		delete_transient( 'scam_dev_updater_sha256' );
		return $return;
	}

	public function auto_update( $update, $item ) {
		return $update;
	}
}

new Scam_Dev_Theme_Updater();
