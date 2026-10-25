<?php
/**
 * Plugin updates and auto-update policy for the Scam Dev suite.
 *
 * The theme ships a signed update manifest (see class-theme-updater.php).
 * This class reuses that same verified payload for the bundled plugins and
 * turns on WordPress auto-updates for the suite only, so unrelated plugins
 * from WordPress.org keep whatever policy the site owner chose.
 *
 * Manifest payload shape:
 *
 * {
 *   "version": "...", "download_url": "...", "sha256": "...",   // theme
 *   "plugins": {
 *     "scam-dev-vk-import": {
 *       "version": "...", "download_url": "...", "sha256": "..."
 *     }
 *   }
 * }
 *
 * @package Scam_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin update client for the bundled suite plugins.
 */
final class Scam_Dev_Suite_Updater {

	/**
	 * Theme updater instance that owns manifest verification.
	 *
	 * @var Scam_Dev_Theme_Updater|null
	 */
	private $theme_updater;

	/**
	 * Verified manifest cache for the current request.
	 *
	 * @var array|WP_Error|null
	 */
	private $manifest = null;

	/**
	 * Constructor.
	 *
	 * @param Scam_Dev_Theme_Updater|null $theme_updater Manifest client.
	 */
	public function __construct( $theme_updater = null ) {
		$this->theme_updater = $theme_updater;

		add_filter(
			'pre_set_site_transient_update_plugins',
			array( $this, 'check_update' )
		);
		add_filter(
			'upgrader_pre_download',
			array( $this, 'download_and_verify_package' ),
			10,
			4
		);
		add_filter( 'auto_update_plugin', array( $this, 'auto_update_plugin' ), 10, 2 );
		add_filter( 'auto_update_theme', array( $this, 'auto_update_theme' ), 10, 2 );
	}

	/**
	 * Read the verified manifest once per request.
	 *
	 * @return array|WP_Error
	 */
	private function manifest() {
		if ( null !== $this->manifest ) {
			return $this->manifest;
		}

		if ( ! $this->theme_updater instanceof Scam_Dev_Theme_Updater ) {
			$this->manifest = new WP_Error(
				'scam_dev_suite_no_client',
				__( 'Клиент обновлений недоступен.', 'scam-dev' )
			);

			return $this->manifest;
		}

		$this->manifest = $this->theme_updater->fetch_verified_manifest();

		return $this->manifest;
	}

	/**
	 * Return the plugin slug for a plugin file path.
	 *
	 * @param string $plugin_file Plugin file relative to the plugins directory.
	 * @return string
	 */
	private function slug_from_file( $plugin_file ) {
		$directory = dirname( $plugin_file );

		if ( '.' !== $directory && '' !== $directory ) {
			return $directory;
		}

		return basename( $plugin_file, '.php' );
	}

	/**
	 * Validate one manifest plugin entry.
	 *
	 * @param mixed $entry Manifest entry.
	 * @return array|false
	 */
	private function sanitize_entry( $entry ) {
		if ( ! is_array( $entry ) ) {
			return false;
		}

		$version = isset( $entry['version'] ) ? (string) $entry['version'] : '';
		$package = isset( $entry['download_url'] ) ? esc_url_raw( $entry['download_url'] ) : '';
		$sha256  = isset( $entry['sha256'] ) ? strtolower( (string) $entry['sha256'] ) : '';

		if ( ! preg_match( '/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/', $version ) ) {
			return false;
		}

		if ( ! preg_match( '/^[a-f0-9]{64}$/', $sha256 ) ) {
			return false;
		}

		if ( ! $this->theme_updater->is_allowed_package_url( $package ) ) {
			return false;
		}

		return array(
			'version'      => $version,
			'download_url' => $package,
			'sha256'       => $sha256,
		);
	}

	/**
	 * Add plugin updates to the update transient.
	 *
	 * @param mixed $transient Plugin update transient.
	 * @return mixed
	 */
	public function check_update( $transient ) {
		if ( ! is_object( $transient ) ) {
			$transient = new stdClass();
		}

		$data = $this->manifest();

		if ( is_wp_error( $data ) || empty( $data['plugins'] ) || ! is_array( $data['plugins'] ) ) {
			return $transient;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$installed = get_plugins();

		if ( ! is_array( $installed ) ) {
			return $transient;
		}

		foreach ( $installed as $plugin_file => $plugin_data ) {
			$slug = $this->slug_from_file( $plugin_file );

			if ( '' === $slug || ! isset( $data['plugins'][ $slug ] ) ) {
				continue;
			}

			$entry = $this->sanitize_entry( $data['plugins'][ $slug ] );

			if ( ! $entry ) {
				continue;
			}

			if ( version_compare( $plugin_data['Version'], $entry['version'], '>=' ) ) {
				continue;
			}

			if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
				$transient->response = array();
			}

			$transient->response[ $plugin_file ] = (object) array(
				'id'           => isset( $data['url'] ) ? (string) $data['url'] : '',
				'slug'         => $slug,
				'plugin'       => $plugin_file,
				'new_version'  => $entry['version'],
				'url'          => isset( $data['url'] ) ? (string) $data['url'] : '',
				'package'      => $entry['download_url'],
				'requires'     => isset( $data['requires'] ) ? (string) $data['requires'] : '6.5',
				'requires_php' => isset( $data['requires_php'] ) ? (string) $data['requires_php'] : '8.0',
			);
		}

		return $transient;
	}

	/**
	 * Download a plugin package and verify its SHA-256 before unpacking.
	 *
	 * @param false|string|WP_Error $reply      Previous filter value.
	 * @param string                $package    Package URL.
	 * @param WP_Upgrader           $upgrader   Upgrader instance.
	 * @param array                 $hook_extra Extra update context.
	 * @return false|string|WP_Error
	 */
	public function download_and_verify_package( $reply, $package, $upgrader, $hook_extra ) {
		unset( $upgrader );

		if ( empty( $hook_extra['plugin'] ) ) {
			return $reply;
		}

		$plugin_file = (string) $hook_extra['plugin'];
		$slug        = $this->slug_from_file( $plugin_file );

		if ( '' === $slug ) {
			return $reply;
		}

		$data = $this->manifest();

		if ( is_wp_error( $data ) || empty( $data['plugins'][ $slug ] ) ) {
			return $reply;
		}

		$entry = $this->sanitize_entry( $data['plugins'][ $slug ] );

		if ( ! $entry ) {
			return new WP_Error(
				'scam_dev_suite_entry',
				__( 'Обновление отклонено: запись манифеста некорректна.', 'scam-dev' )
			);
		}

		if ( $package !== $entry['download_url'] ) {
			return new WP_Error(
				'scam_dev_suite_package_mismatch',
				__( 'Обновление отклонено: пакет не совпадает с манифестом.', 'scam-dev' )
			);
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

		if ( ! is_string( $actual_hash ) || ! hash_equals( $entry['sha256'], $actual_hash ) ) {
			wp_delete_file( $temp_file );

			return new WP_Error(
				'scam_dev_suite_hash_mismatch',
				__( 'Обновление отклонено: контрольная сумма пакета не совпадает.', 'scam-dev' )
			);
		}

		return $temp_file;
	}

	/**
	 * Enable auto-updates for the bundled plugins only.
	 *
	 * @param bool|null $update Whether to auto-update.
	 * @param object    $item   Update offer.
	 * @return bool|null
	 */
	public function auto_update_plugin( $update, $item ) {
		if ( ! isset( $item->plugin ) ) {
			return $update;
		}

		$slug = $this->slug_from_file( (string) $item->plugin );

		if ( '' === $slug || 0 !== strpos( $slug, 'scam-dev-' ) ) {
			return $update;
		}

		$data = $this->manifest();

		if ( is_wp_error( $data ) || empty( $data['plugins'] ) || ! is_array( $data['plugins'] ) ) {
			return $update;
		}

		return isset( $data['plugins'][ $slug ] ) ? true : $update;
	}

	/**
	 * Enable auto-updates for this theme only.
	 *
	 * @param bool|null $update Whether to auto-update.
	 * @param object    $item   Update offer.
	 * @return bool|null
	 */
	public function auto_update_theme( $update, $item ) {
		if ( ! isset( $item->theme ) || 'scam-dev' !== $item->theme ) {
			return $update;
		}

		return true;
	}
}
