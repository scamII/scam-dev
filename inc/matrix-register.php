<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- This file is loaded via functions.php, not autoloaded.

/**
 * Matrix Registration handler.
 *
 * @package Scam_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Scam_Dev_Matrix_Register
 *
 * Handles Matrix user registration form, page creation, and menu integration.
 */
class Scam_Dev_Matrix_Register {


	/**
	 * Кэш страницы регистрации.
	 *
	 * @var WP_Post|false|null
	 */
	private static $registration_page = null;

	/**
	 * Initializes hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'handle_registration' ) );
		add_action( 'after_switch_theme', array( __CLASS__, 'create_registration_page' ) );
		add_action( 'init', array( __CLASS__, 'maybe_create_registration_page' ) );
		add_filter( 'wp_nav_menu_items', array( __CLASS__, 'add_menu_link' ), 10, 2 );
	}

	/**
	 * Возвращает объект страницы регистрации с кэшированием.
	 *
	 * @return WP_Post|false
	 */
	private static function get_registration_page() {
		if ( null !== self::$registration_page ) {
			return self::$registration_page;
		}

		$pages = get_pages(
			array(
				'meta_key'   => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value' => 'page-matrix-register.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'number'     => 1,
			)
		);

		self::$registration_page = ! empty( $pages ) ? $pages[0] : false;
		return self::$registration_page;
	}

	/**
	 * Автосоздание страницы регистрации при активации темы.
	 */
	public static function create_registration_page() {
		$page = self::get_registration_page();

		if ( ! $page ) {
			wp_insert_post(
				array(
					'post_title'   => 'Matrix Регистрация',
					'post_name'    => 'matrix-register',
					'post_content' => '',
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'meta_input'   => array(
						'_wp_page_template' => 'page-matrix-register.php',
					),
				)
			);
			// Сброс кэша после создания.
			self::$registration_page = null;
		}
	}

	/**
	 * Проверяет наличие страницы при каждом init (на случай ручного удаления).
	 */
	public static function maybe_create_registration_page() {
		static $checked = false;
		if ( $checked ) {
			return;
		}
		$checked = true;

		$page = self::get_registration_page();

		if ( ! $page ) {
			self::create_registration_page();
		}
	}

	/**
	 * Добавляет выпадающий пункт «Matrix» с «Регистрация» и «Вход» в основное меню.
	 *
	 * @param string $items Menu HTML.
	 * @param object $args  Menu arguments.
	 * @return string Modified menu HTML.
	 */
	public static function add_menu_link( $items, $args ) {
		if ( 'primary' !== $args->theme_location ) {
			return $items;
		}

		$page = self::get_registration_page();

		$register_url = $page ? get_permalink( $page ) : home_url( '/matrix-register/' );
		$chat_url     = 'https://chat.scam-dev.ru';

		$dropdown  = '<li class="relative group">';
		$dropdown .= '<a href="#" class="block px-4 py-2 rounded-lg text-sm font-medium transition-colors text-gray-300 hover:text-white hover:bg-slate-500/10" onclick="return false">';
		$dropdown .= 'Matrix';
		$dropdown .= '<svg class="inline-block w-3 h-3 ml-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
		$dropdown .= '</a>';
		$dropdown .= '<ul class="absolute top-full right-0 w-48 rounded-xl shadow-2xl py-2 z-50 hidden group-hover:block" style="background:var(--color-header-bg);backdrop-filter:blur(20px);border:2px solid var(--color-border, rgba(255,255,255,0.08));box-shadow:var(--shadow-md)">';
		$dropdown .= '<li><a href="' . esc_url( $register_url ) . '" class="block px-4 py-2 text-sm text-coral-400 hover:text-white hover:bg-slate-500/10 transition-colors">Регистрация</a></li>';
		$dropdown .= '<li><a href="' . esc_url( $chat_url ) . '" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-slate-500/10 transition-colors" target="_blank" rel="noopener">Вход</a></li>';
		$dropdown .= '</ul>';
		$dropdown .= '</li>';

		// Вставляем перед пунктом «Войти».
		$login_pos = strpos( $items, '>Войти<' );
		if ( false !== $login_pos ) {
			$li_start = strrpos( substr( $items, 0, $login_pos ), '<li' );
			if ( false !== $li_start ) {
				$items = substr_replace( $items, $dropdown, $li_start, 0 );
				return $items;
			}
		}

		// Если «Войти» не найден — добавляем в конец.
		$items .= $dropdown;
		return $items;
	}

	/**
	 * Обрабатывает POST-запрос формы регистрации.
	 */
	public static function handle_registration() {
		if ( ! isset( $_POST['matrix_register'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ?? '' ), 'matrix_register' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- wp_unslash is called; nonce does not need sanitizing.
			wp_die( 'Ошибка проверки безопасности. Пожалуйста, обновите страницу и попробуйте снова.' );
		}

		// Rate limiting: не более 3 попыток за 10 минут с одного IP.
		$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$rate_key = 'matrix_reg_rate_' . md5( $ip );
		$attempts = (int) get_transient( $rate_key );

		if ( $attempts >= 3 ) {
			$form_errors = array( 'Слишком много попыток регистрации. Пожалуйста, подождите 10 минут.' );
			set_transient( 'matrix_register_errors', $form_errors, 60 );
			wp_safe_redirect( wp_get_referer() ? wp_get_referer() : home_url() );
			exit;
		}

		$username         = sanitize_user( wp_unslash( $_POST['mx_username'] ?? '' ) );
		$password         = wp_unslash( $_POST['mx_password'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Passwords are not sanitized intentionally.
		$password_confirm = wp_unslash( $_POST['mx_password_confirm'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Passwords are not sanitized intentionally.

		$form_errors = array();

		if ( empty( $username ) || strlen( $username ) < 3 ) {
			$form_errors[] = 'Имя пользователя должно быть не менее 3 символов.';
		}

		if ( ! preg_match( '/^[a-z0-9._\-]+$/i', $username ) ) {
			$form_errors[] = 'Имя пользователя может содержать только латинские буквы, цифры, точки, дефисы и подчёркивания.';
		}

		if ( strlen( $password ) < 8 ) {
			$form_errors[] = 'Пароль должен быть не менее 8 символов.';
		}

		if ( $password !== $password_confirm ) {
			$form_errors[] = 'Пароли не совпадают.';
		}

		if ( empty( wp_unslash( $_POST['mx_tos'] ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Checkbox value, validation only.
			$form_errors[] = 'Необходимо принять условия использования и политику конфиденциальности.';
		}

		if ( ! empty( $form_errors ) ) {
			set_transient( 'matrix_register_errors', $form_errors, 60 );
			set_transient( 'matrix_register_username', $username, 60 );
			// Увеличиваем счётчик попыток.
			set_transient( $rate_key, $attempts + 1, 600 );
			wp_safe_redirect( wp_get_referer() ? wp_get_referer() : home_url() );
			exit;
		}

		$result = self::create_matrix_user( $username, $password );

		if ( is_wp_error( $result ) ) {
			set_transient( 'matrix_register_errors', array( $result->get_error_message() ), 60 );
			set_transient( 'matrix_register_username', $username, 60 );
			set_transient( $rate_key, $attempts + 1, 600 );
			wp_safe_redirect( wp_get_referer() ? wp_get_referer() : home_url() );
			exit;
		}

		// Успешная регистрация — сбрасываем счётчик.
		delete_transient( $rate_key );
		set_transient( 'matrix_register_success', true, 60 );
		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : home_url() );
		exit;
	}

	/**
	 * Создаёт пользователя Matrix через Synapse Admin API.
	 *
	 * @param string $username Локальная часть Matrix ID.
	 * @param string $password Пароль пользователя.
	 * @return array|WP_Error Массив с ответом API или объект ошибки.
	 */
	private static function create_matrix_user( $username, $password ) {
		$homeserver = defined( 'MATRIX_HOMESERVER_URL' ) ? MATRIX_HOMESERVER_URL : '';
		$token      = defined( 'MATRIX_ADMIN_TOKEN' ) ? MATRIX_ADMIN_TOKEN : '';

		if ( empty( $homeserver ) || empty( $token ) ) {
			return new WP_Error( 'config', 'Matrix-сервер не настроен. Обратитесь к администратору сайта.' );
		}

		$domain = wp_parse_url( $homeserver, PHP_URL_HOST );
		if ( ! $domain ) {
			return new WP_Error( 'config', 'Неверный URL Matrix-сервера.' );
		}

		$user_id  = '@' . $username . ':' . $domain;
		$api_base = untrailingslashit( $homeserver ) . '/_synapse/admin';

		// ── Попытка №1: Synapse ≥ 1.66 (создание пользователя с паролем одним запросом). ──
		$response = wp_remote_request(
			$api_base . '/v2/users/' . rawurlencode( $user_id ),
			array(
				'method'  => 'PUT',
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'password'    => $password,
						'displayname' => $username,
						'admin'       => false,
						'deactivated' => false,
					)
				),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'network',
				'Ошибка связи с Matrix-сервером: ' . $response->get_error_message()
			);
		}

		$status = wp_remote_retrieve_response_code( $response );
		$body   = json_decode( wp_remote_retrieve_body( $response ), true );

		// Пользователь уже существует.
		if ( 400 === $status && isset( $body['errcode'] ) && 'M_USER_IN_USE' === $body['errcode'] ) {
			return new WP_Error( 'exists', 'Пользователь с таким именем уже существует.' );
		}

		if ( 200 === $status || 201 === $status ) {
			return $body;
		}

		// Fallback на старый API только если v2 эндпоинт не существует (404/405/501).
		if ( 404 !== $status && 405 !== $status && 501 !== $status ) {
			return new WP_Error(
				'server',
				'Не удалось создать пользователя. Код ответа сервера: ' . $status
			);
		}

		// ── Попытка №2: старые версии Synapse (создать пользователя, затем задать пароль). ──
		$response = wp_remote_request(
			$api_base . '/v2/users/' . rawurlencode( $user_id ),
			array(
				'method'  => 'PUT',
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( array( 'displayname' => $username ) ),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'network', 'Ошибка связи с Matrix-сервером: ' . $response->get_error_message() );
		}

		$status = wp_remote_retrieve_response_code( $response );
		$body   = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 400 === $status && isset( $body['errcode'] ) && 'M_USER_IN_USE' === $body['errcode'] ) {
			return new WP_Error( 'exists', 'Пользователь с таким именем уже существует.' );
		}

		if ( 200 !== $status && 201 !== $status ) {
			return new WP_Error(
				'server',
				'Не удалось создать пользователя. Код ответа сервера: ' . $status
			);
		}

		// Установка пароля через reset_password.
		$response = wp_remote_request(
			$api_base . '/v1/reset_password/' . rawurlencode( $user_id ),
			array(
				'method'  => 'POST',
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( array( 'new_password' => $password ) ),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'password',
				'Пользователь создан, но не удалось установить пароль. Пожалуйста, свяжитесь с администратором.'
			);
		}

		$status = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status && 201 !== $status ) {
			return new WP_Error(
				'password',
				'Пользователь создан, но не удалось установить пароль (код ' . $status . '). Свяжитесь с администратором.'
			);
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}
}

Scam_Dev_Matrix_Register::init();
