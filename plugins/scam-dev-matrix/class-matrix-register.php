<?php
/**
 * Matrix registration controller and provisioning service.
 *
 * @package Scam_Dev_Matrix
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Matrix registration controller.
 */
final class Scam_Dev_Matrix_Registration_Service {

	/**
	 * Cache of the registration page.
	 *
	 * @var WP_Post|false|null
	 */
	private static $registration_page = null;

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'handle_registration' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_repair_page' ) );
		add_filter( 'wp_nav_menu_items', array( __CLASS__, 'add_menu_link' ), 10, 2 );
	}

	/**
	 * Plugin activation.
	 */
	public static function activate() {
		self::create_or_update_registration_page();
	}

	/**
	 * Ensure the registration page exists when an administrator visits wp-admin.
	 */
	public static function maybe_repair_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$page = self::get_registration_page();
		$template = $page
			? get_post_meta( $page->ID, '_wp_page_template', true )
			: '';

		if ( ! $page || SCAM_DEV_MATRIX_TEMPLATE !== $template ) {
			self::create_or_update_registration_page();
		}
	}

	/**
	 * Return the registration page.
	 *
	 * @return WP_Post|false
	 */
	public static function get_registration_page() {
		if ( null !== self::$registration_page ) {
			return self::$registration_page;
		}

		$page = get_page_by_path( 'matrix-register', OBJECT, 'page' );

		self::$registration_page = $page instanceof WP_Post ? $page : false;
		return self::$registration_page;
	}

	/**
	 * Create or update the plugin page.
	 *
	 * @return int|WP_Error
	 */
	public static function create_or_update_registration_page() {
		$page = self::get_registration_page();

		if ( $page ) {
			update_post_meta(
				$page->ID,
				'_wp_page_template',
				SCAM_DEV_MATRIX_TEMPLATE
			);
			return $page->ID;
		}

		$result = wp_insert_post(
			array(
				'post_title'   => __( 'Matrix Регистрация', 'scam-dev-matrix' ),
				'post_name'    => 'matrix-register',
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'meta_input'   => array(
					'_wp_page_template' => SCAM_DEV_MATRIX_TEMPLATE,
				),
			),
			true
		);

		self::$registration_page = null;
		return $result;
	}

	/**
	 * Add Matrix links to the primary menu.
	 *
	 * @param string   $items Existing menu HTML.
	 * @param stdClass $args  Menu arguments.
	 * @return string
	 */
	public static function add_menu_link( $items, $args ) {
		if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
			return $items;
		}

		$page         = self::get_registration_page();
		$register_url = $page
			? get_permalink( $page )
			: home_url( '/matrix-register/' );
		$chat_url     = defined( 'MATRIX_CHAT_URL' )
			? (string) MATRIX_CHAT_URL
			: 'https://chat.scam-dev.ru';

		$top_link_classes = implode(
			' ',
			array(
				'block px-4 py-2 rounded-lg text-sm font-medium',
				'transition-colors text-gray-300 hover:text-white',
				'hover:bg-slate-500/10',
			)
		);
		$submenu_classes = implode(
			' ',
			array(
				'absolute top-full right-0 w-48 rounded-xl shadow-2xl',
				'py-2 z-50 hidden group-hover:block',
				'group-focus-within:block account-submenu',
			)
		);
		$register_classes = implode(
			' ',
			array(
				'block px-4 py-2 text-sm text-coral-400',
				'hover:text-white hover:bg-slate-500/10',
			)
		);
		$chat_classes = implode(
			' ',
			array(
				'block px-4 py-2 text-sm text-gray-400',
				'hover:text-white hover:bg-slate-500/10',
			)
		);

		$dropdown  = '<li class="relative group">';
		$dropdown .= '<a href="' . esc_url( $register_url ) . '"';
		$dropdown .= ' class="' . esc_attr( $top_link_classes ) . '">';
		$dropdown .= esc_html__( 'Matrix', 'scam-dev-matrix' );
		$dropdown .= ' <span aria-hidden="true">▾</span></a>';
		$dropdown .= '<ul class="' . esc_attr( $submenu_classes ) . '">';
		$dropdown .= '<li><a href="' . esc_url( $register_url ) . '"';
		$dropdown .= ' class="' . esc_attr( $register_classes ) . '">';
		$dropdown .= esc_html__( 'Регистрация', 'scam-dev-matrix' );
		$dropdown .= '</a></li>';
		$dropdown .= '<li><a href="' . esc_url( $chat_url ) . '"';
		$dropdown .= ' class="' . esc_attr( $chat_classes ) . '"';
		$dropdown .= ' target="_blank" rel="noopener noreferrer">';
		$dropdown .= esc_html__( 'Вход', 'scam-dev-matrix' );
		$dropdown .= '</a></li></ul></li>';

		$login_position = strpos( $items, '>' . __( 'Войти', 'scam-dev' ) . '<' );

		if ( false !== $login_position ) {
			$list_item_start = strrpos(
				substr( $items, 0, $login_position ),
				'<li'
			);

			if ( false !== $list_item_start ) {
				return substr_replace(
					$items,
					$dropdown,
					$list_item_start,
					0
				);
			}
		}

		return $items . $dropdown;
	}

	/**
	 * Whether registration is safely enabled.
	 *
	 * Registration is closed by default and requires at least one anti-abuse
	 * mechanism: an invite code or Cloudflare Turnstile.
	 *
	 * @return bool
	 */
	public static function registration_is_enabled() {
		$enabled = defined( 'SCAM_DEV_MATRIX_REGISTRATION_ENABLED' )
			&& true === SCAM_DEV_MATRIX_REGISTRATION_ENABLED;

		return $enabled
			&& ( self::invite_is_configured() || self::turnstile_is_configured() )
			&& ! is_wp_error( self::get_matrix_config() );
	}

	/**
	 * Whether invite-code validation is configured.
	 *
	 * @return bool
	 */
	public static function invite_is_configured() {
		return defined( 'SCAM_DEV_MATRIX_INVITE_CODE' )
			&& '' !== trim( (string) SCAM_DEV_MATRIX_INVITE_CODE );
	}

	/**
	 * Whether Turnstile validation is configured.
	 *
	 * @return bool
	 */
	public static function turnstile_is_configured() {
		return defined( 'SCAM_DEV_TURNSTILE_SITE_KEY' )
			&& defined( 'SCAM_DEV_TURNSTILE_SECRET_KEY' )
			&& '' !== trim( (string) SCAM_DEV_TURNSTILE_SITE_KEY )
			&& '' !== trim( (string) SCAM_DEV_TURNSTILE_SECRET_KEY );
	}

	/**
	 * Return the public Turnstile site key.
	 *
	 * @return string
	 */
	public static function get_turnstile_site_key() {
		return self::turnstile_is_configured()
			? (string) SCAM_DEV_TURNSTILE_SITE_KEY
			: '';
	}

	/**
	 * Consume one-time form state.
	 *
	 * @param string $token Public random state token.
	 * @return array
	 */
	public static function consume_state( $token ) {
		if ( ! is_string( $token ) || ! preg_match( '/^[a-f0-9]{64}$/', $token ) ) {
			return array();
		}

		$key   = self::get_state_key( $token );
		$state = get_transient( $key );
		delete_transient( $key );

		return is_array( $state ) ? $state : array();
	}

	/**
	 * Handle a public registration POST.
	 */
	public static function handle_registration() {
		$submitted = isset( $_POST['matrix_register'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
			? sanitize_text_field( wp_unslash( $_POST['matrix_register'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
			: '';

		if ( '1' !== $submitted ) {
			return;
		}

		if ( ! self::registration_is_enabled() ) {
			self::redirect_with_state(
				array(
					'errors' => array(
						__( 'Публичная регистрация сейчас закрыта.', 'scam-dev-matrix' ),
					),
				)
			);
		}

		$nonce = isset( $_POST['_wpnonce'] )
			? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) )
			: '';

		if ( ! wp_verify_nonce( $nonce, 'matrix_register' ) ) {
			self::audit_log( 'nonce_failed', '', '' );
			self::redirect_with_state(
				array(
					'errors' => array(
						__( 'Сессия формы истекла. Обновите страницу и повторите попытку.', 'scam-dev-matrix' ),
					),
				)
			);
		}

		$ip = self::get_request_ip();

		if ( ! $ip ) {
			self::redirect_with_state(
				array(
					'errors' => array(
						__( 'Не удалось проверить источник запроса.', 'scam-dev-matrix' ),
					),
				)
			);
		}

		$ip_hash       = hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) );
		$attempt_key   = 'mx_attempt_' . substr( $ip_hash, 0, 40 );
		$success_key   = 'mx_success_' . substr( $ip_hash, 0, 40 );
		$attempts      = (int) get_transient( $attempt_key );
		$successes     = (int) get_transient( $success_key );
		$attempt_limit = defined( 'SCAM_DEV_MATRIX_ATTEMPT_LIMIT' )
			? max( 1, (int) SCAM_DEV_MATRIX_ATTEMPT_LIMIT )
			: 5;
		$success_limit = defined( 'SCAM_DEV_MATRIX_IP_DAILY_LIMIT' )
			? max( 1, (int) SCAM_DEV_MATRIX_IP_DAILY_LIMIT )
			: 2;

		if ( $attempts >= $attempt_limit ) {
			self::audit_log( 'rate_limited', $ip_hash, '' );
			self::redirect_with_state(
				array(
					'errors' => array(
						__( 'Слишком много попыток. Повторите через 15 минут.', 'scam-dev-matrix' ),
					),
				)
			);
		}

		set_transient(
			$attempt_key,
			$attempts + 1,
			15 * MINUTE_IN_SECONDS
		);

		if ( $successes >= $success_limit ) {
			self::audit_log( 'ip_daily_limit', $ip_hash, '' );
			self::redirect_with_state(
				array(
					'errors' => array(
						__( 'Дневной лимит регистраций для этого адреса исчерпан.', 'scam-dev-matrix' ),
					),
				)
			);
		}

		$global_key   = 'mx_global_' . gmdate( 'Ymd' );
		$global_count = (int) get_transient( $global_key );
		$global_limit = defined( 'SCAM_DEV_MATRIX_GLOBAL_DAILY_LIMIT' )
			? max( 1, (int) SCAM_DEV_MATRIX_GLOBAL_DAILY_LIMIT )
			: 20;

		if ( $global_count >= $global_limit ) {
			self::audit_log( 'global_daily_limit', $ip_hash, '' );
			self::redirect_with_state(
				array(
					'errors' => array(
						__( 'Дневной лимит регистраций исчерпан. Повторите завтра.', 'scam-dev-matrix' ),
					),
				)
			);
		}

		$username = isset( $_POST['mx_username'] )
			? sanitize_user( wp_unslash( $_POST['mx_username'] ), true )
			: '';

		// Passwords must be compared and forwarded verbatim after nonce validation.
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$password = isset( $_POST['mx_password'] )
			? (string) wp_unslash( $_POST['mx_password'] )
			: '';
		$password_confirm = isset( $_POST['mx_password_confirm'] )
			? (string) wp_unslash( $_POST['mx_password_confirm'] )
			: '';
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$errors = self::validate_form(
			$username,
			$password,
			$password_confirm,
			$ip
		);

		if ( $errors ) {
			self::audit_log( 'validation_failed', $ip_hash, $username );
			self::redirect_with_state(
				array(
					'errors'   => $errors,
					'username' => $username,
				)
			);
		}

		$result = self::create_matrix_user( $username, $password );

		if ( is_wp_error( $result ) ) {
			self::audit_log(
				'provisioning_failed:' . $result->get_error_code(),
				$ip_hash,
				$username
			);
			self::redirect_with_state(
				array(
					'errors'   => array(
						__( 'Не удалось завершить регистрацию. Повторите позже или обратитесь к администратору.', 'scam-dev-matrix' ),
					),
					'username' => $username,
				)
			);
		}

		set_transient( $success_key, $successes + 1, DAY_IN_SECONDS );
		set_transient( $global_key, $global_count + 1, DAY_IN_SECONDS );

		self::audit_log( 'success', $ip_hash, $username );
		self::redirect_with_state(
			array(
				'success'  => true,
				'username' => $username,
			)
		);
	}

	/**
	 * Validate form fields and anti-abuse controls.
	 *
	 * @param string $username Matrix localpart.
	 * @param string $password Password.
	 * @param string $password_confirm Password confirmation.
	 * @param string $ip Request IP.
	 * @return string[]
	 */
	private static function validate_form( $username, $password, $password_confirm, $ip ) {
		$errors = array();

		if ( strlen( $username ) < 3 || strlen( $username ) > 64 ) {
			$errors[] = __( 'Имя должно содержать от 3 до 64 символов.', 'scam-dev-matrix' );
		}

		if ( ! preg_match( '/^[a-z0-9._=-]+$/', $username ) ) {
			$errors[] = __( 'Используйте строчные латинские буквы, цифры, точки, дефисы, подчёркивания и знак равенства.', 'scam-dev-matrix' );
		}

		if ( strlen( $password ) < 12 || strlen( $password ) > 128 ) {
			$errors[] = __( 'Пароль должен содержать от 12 до 128 символов.', 'scam-dev-matrix' );
		}

		if ( $password !== $password_confirm ) {
			$errors[] = __( 'Пароли не совпадают.', 'scam-dev-matrix' );
		}

		$terms_accepted = isset( $_POST['mx_tos'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
			? sanitize_text_field( wp_unslash( $_POST['mx_tos'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
			: '';

		if ( '1' !== $terms_accepted ) {
			$errors[] = __( 'Необходимо принять условия использования и политику конфиденциальности.', 'scam-dev-matrix' );
		}

		if ( self::invite_is_configured() ) {
			// The shared secret must be compared verbatim after controller nonce validation.
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$invite = isset( $_POST['mx_invite_code'] )
				? trim( (string) wp_unslash( $_POST['mx_invite_code'] ) )
				: '';
			// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			// phpcs:enable WordPress.Security.NonceVerification.Missing

			if ( strlen( $invite ) > 128
				|| ! hash_equals( (string) SCAM_DEV_MATRIX_INVITE_CODE, $invite )
			) {
				$errors[] = __( 'Недействительный код приглашения.', 'scam-dev-matrix' );
			}
		}

		if ( self::turnstile_is_configured()
			&& ! self::verify_turnstile( $ip )
		) {
			$errors[] = __( 'Проверка защиты от автоматических регистраций не пройдена.', 'scam-dev-matrix' );
		}

		return $errors;
	}

	/**
	 * Verify a Cloudflare Turnstile response.
	 *
	 * @param string $ip Request IP.
	 * @return bool
	 */
	private static function verify_turnstile( $ip ) {
		// Nonce validation is performed by the registration controller.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$token = isset( $_POST['cf-turnstile-response'] )
			? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) )
			: '';
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( ! $token || strlen( $token ) > 2048 ) {
			return false;
		}

		$response = wp_safe_remote_post(
			'https://challenges.cloudflare.com/turnstile/v0/siteverify',
			array(
				'timeout'     => 5,
				'redirection' => 0,
				'body'        => array(
					'secret'   => (string) SCAM_DEV_TURNSTILE_SECRET_KEY,
					'response' => $token,
					'remoteip' => $ip,
				),
			)
		);

		if ( is_wp_error( $response )
			|| 200 !== wp_remote_retrieve_response_code( $response )
		) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $body ) || empty( $body['success'] ) ) {
			return false;
		}

		$expected_host = defined( 'SCAM_DEV_TURNSTILE_EXPECTED_HOST' )
			? strtolower( trim( (string) SCAM_DEV_TURNSTILE_EXPECTED_HOST ) )
			: strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
		$response_host = isset( $body['hostname'] )
			? strtolower( sanitize_text_field( $body['hostname'] ) )
			: '';

		return '' !== $expected_host && hash_equals( $expected_host, $response_host );
	}

	/**
	 * Provision a Matrix account through the Synapse v2 Admin API.
	 *
	 * @param string $username Matrix localpart.
	 * @param string $password Password.
	 * @return array|WP_Error
	 */
	private static function create_matrix_user( $username, $password ) {
		$config = self::get_matrix_config();

		if ( is_wp_error( $config ) ) {
			return $config;
		}

		$user_id = '@' . $username . ':' . $config['domain'];
		$url     = $config['homeserver']
			. '/_synapse/admin/v2/users/'
			. rawurlencode( $user_id );

		$response = wp_safe_remote_request(
			$url,
			array(
				'method'      => 'PUT',
				'timeout'     => 10,
				'redirection' => 0,
				'headers'     => array(
					'Authorization' => 'Bearer ' . $config['token'],
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
				),
				'body'        => wp_json_encode(
					array(
						'password'    => $password,
						'displayname' => $username,
						'admin'       => false,
						'deactivated' => false,
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			self::log_internal_error( 'matrix_http', $response->get_error_message() );
			return new WP_Error( 'matrix_network' );
		}

		$status = wp_remote_retrieve_response_code( $response );
		$body   = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $status && 201 !== $status ) {
			$error_code = is_array( $body ) && isset( $body['errcode'] )
				? sanitize_key( $body['errcode'] )
				: 'unknown';
			self::log_internal_error(
				'matrix_status',
				'HTTP ' . $status . ' errcode=' . $error_code
			);
			return new WP_Error( 'matrix_status' );
		}

		if ( ! is_array( $body ) ) {
			self::log_internal_error( 'matrix_json', 'Malformed JSON response.' );
			return new WP_Error( 'matrix_json' );
		}

		return $body;
	}

	/**
	 * Validate Matrix configuration.
	 *
	 * @return array|WP_Error
	 */
	private static function get_matrix_config() {
		$homeserver = defined( 'MATRIX_HOMESERVER_URL' )
			? untrailingslashit( (string) MATRIX_HOMESERVER_URL )
			: '';
		$token = defined( 'MATRIX_ADMIN_TOKEN' )
			? trim( (string) MATRIX_ADMIN_TOKEN )
			: '';

		$scheme = wp_parse_url( $homeserver, PHP_URL_SCHEME );
		$host   = wp_parse_url( $homeserver, PHP_URL_HOST );
		$port   = wp_parse_url( $homeserver, PHP_URL_PORT );
		$user   = wp_parse_url( $homeserver, PHP_URL_USER );
		$pass   = wp_parse_url( $homeserver, PHP_URL_PASS );

		if ( ! $token
			|| 'https' !== $scheme
			|| ! $host
			|| null !== $port
			|| null !== $user
			|| null !== $pass
		) {
			return new WP_Error( 'matrix_config' );
		}

		$expected_host = defined( 'MATRIX_EXPECTED_HOST' )
			? strtolower( trim( (string) MATRIX_EXPECTED_HOST ) )
			: '';

		if ( ! $expected_host || $expected_host !== strtolower( $host ) ) {
			return new WP_Error( 'matrix_host' );
		}

		return array(
			'homeserver' => $homeserver,
			'domain'     => strtolower( $host ),
			'token'      => $token,
		);
	}

	/**
	 * Return a validated direct peer IP.
	 *
	 * Proxies must be configured at the web-server layer so REMOTE_ADDR is the
	 * real client address. Untrusted forwarding headers are intentionally ignored.
	 *
	 * @return string
	 */
	private static function get_request_ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] )
			? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
			: '';

		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
	}

	/**
	 * Store a one-time state and redirect to the known registration page.
	 *
	 * @param array $state Form state.
	 */
	private static function redirect_with_state( $state ) {
		try {
			$token = bin2hex( random_bytes( 32 ) );
		} catch ( Throwable ) {
			$token = hash_hmac(
				'sha256',
				wp_generate_uuid4() . wp_generate_password( 64, true, true ),
				wp_salt( 'auth' )
			);
		}

		set_transient(
			self::get_state_key( $token ),
			$state,
			2 * MINUTE_IN_SECONDS
		);

		$page = self::get_registration_page();
		$url  = $page ? get_permalink( $page ) : home_url( '/matrix-register/' );
		$url  = add_query_arg( 'matrix_state', $token, $url );

		wp_safe_redirect( $url, 303 );
		exit;
	}

	/**
	 * Build a one-time state key.
	 *
	 * @param string $token Public random token.
	 * @return string
	 */
	private static function get_state_key( $token ) {
		return 'mx_state_' . hash_hmac(
			'sha256',
			$token,
			wp_salt( 'auth' )
		);
	}

	/**
	 * Emit a privacy-preserving audit event.
	 *
	 * @param string $result Result code.
	 * @param string $ip_hash Hashed IP.
	 * @param string $username Matrix username.
	 */
	private static function audit_log( $result, $ip_hash, $username ) {
		$record = array(
			'timestamp' => gmdate( 'c' ),
			'result'    => sanitize_key( $result ),
			'ip_hash'   => $ip_hash ? substr( $ip_hash, 0, 16 ) : '',
			'username'  => sanitize_user( $username, true ),
		);

		do_action( 'scam_dev_matrix_audit', $record );

		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( 'scam-dev-matrix ' . wp_json_encode( $record ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}

	/**
	 * Log a detailed internal error without exposing it to visitors.
	 *
	 * @param string $code Internal code.
	 * @param string $message Internal detail.
	 */
	private static function log_internal_error( $code, $message ) {
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				'scam-dev-matrix internal '
				. sanitize_key( $code )
				. ': '
				. sanitize_text_field( $message )
			);
		}
	}
}
