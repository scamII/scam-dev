<?php
/**
 * Matrix registration page template.
 *
 * @package Scam_Dev_Matrix
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// This high-entropy one-time token only reads and deletes transient display state.
// phpcs:disable WordPress.Security.NonceVerification.Recommended
$state_token = isset( $_GET['matrix_state'] )
	? sanitize_text_field( wp_unslash( $_GET['matrix_state'] ) )
	: '';
// phpcs:enable WordPress.Security.NonceVerification.Recommended
$state       = Scam_Dev_Matrix_Registration_Service::consume_state( $state_token );
$errors      = ! empty( $state['errors'] ) && is_array( $state['errors'] )
	? $state['errors']
	: array();
$success     = ! empty( $state['success'] );
$old_user    = isset( $state['username'] )
	? sanitize_user( $state['username'], true )
	: '';
$enabled     = Scam_Dev_Matrix_Registration_Service::registration_is_enabled();
$homeserver_host = wp_parse_url(
	defined( 'MATRIX_HOMESERVER_URL' )
		? (string) MATRIX_HOMESERVER_URL
		: '',
	PHP_URL_HOST
);
?>

<section class="scamdev-matrix-page">
	<header class="scamdev-matrix-hero">
		<div class="scamdev-matrix-container">
			<h1><?php echo esc_html( get_the_title() ); ?></h1>
			<p>
				<?php esc_html_e( 'Создание учётной записи на защищённом Matrix-сервере.', 'scam-dev-matrix' ); ?>
			</p>
		</div>
	</header>

	<div class="scamdev-matrix-container scamdev-matrix-content">
		<?php if ( $errors ) : ?>
			<div class="scamdev-matrix-alert scamdev-matrix-alert--error"
				role="alert">
				<ul>
					<?php foreach ( $errors as $error ) : ?>
						<li><?php echo esc_html( $error ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ( $success ) : ?>
			<div class="scamdev-matrix-card scamdev-matrix-success"
				role="status">
				<h2><?php esc_html_e( 'Регистрация завершена', 'scam-dev-matrix' ); ?></h2>
				<p>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: Matrix username. */
							__( 'Аккаунт %s создан. Пароль нигде не сохранялся на сайте.', 'scam-dev-matrix' ),
							'@' . $old_user
						)
					);
					?>
				</p>
				<a href="<?php echo esc_url( defined( 'MATRIX_CHAT_URL' ) ? MATRIX_CHAT_URL : 'https://chat.scam-dev.ru' ); ?>"
					class="scamdev-matrix-button"
					target="_blank"
					rel="noopener noreferrer">
					<?php esc_html_e( 'Перейти в чат', 'scam-dev-matrix' ); ?>
				</a>
			</div>
		<?php elseif ( ! $enabled ) : ?>
			<div class="scamdev-matrix-card" role="status">
				<h2><?php esc_html_e( 'Регистрация закрыта', 'scam-dev-matrix' ); ?></h2>
				<p>
					<?php
					esc_html_e(
						'Администратор не включил публичную регистрацию или не настроил обязательную защиту от автоматических регистраций.',
						'scam-dev-matrix'
					);
					?>
				</p>
			</div>
		<?php else : ?>
			<form method="post"
				action="<?php echo esc_url( get_permalink() ); ?>"
				class="scamdev-matrix-card scamdev-matrix-form">
				<?php wp_nonce_field( 'matrix_register' ); ?>

				<div class="scamdev-matrix-field">
					<label for="mx_username">
						<?php esc_html_e( 'Имя пользователя', 'scam-dev-matrix' ); ?>
					</label>
					<input type="text"
						name="mx_username"
						id="mx_username"
						value="<?php echo esc_attr( $old_user ); ?>"
						required
						minlength="3"
						maxlength="64"
						pattern="[a-z0-9._=\-]+"
						placeholder="username"
						autocomplete="username"
						autocapitalize="none"
						spellcheck="false">
					<p class="scamdev-matrix-help">
						<?php esc_html_e( 'Строчные латинские буквы, цифры, точки, дефисы, подчёркивания и знак равенства.', 'scam-dev-matrix' ); ?>
						<?php if ( $homeserver_host ) : ?>
							<br>
							<?php
							echo esc_html(
								sprintf(
									/* translators: 1: username example, 2: homeserver host. */
									__( 'Ваш ID будет выглядеть как @%1$s:%2$s', 'scam-dev-matrix' ),
									'username',
									$homeserver_host
								)
							);
							?>
						<?php endif; ?>
					</p>
				</div>

				<div class="scamdev-matrix-field">
					<label for="mx_password">
						<?php esc_html_e( 'Пароль', 'scam-dev-matrix' ); ?>
					</label>
					<div class="scamdev-matrix-password">
						<input type="password"
							name="mx_password"
							id="mx_password"
							required
							minlength="12"
							maxlength="128"
							autocomplete="new-password">
						<button type="button"
							class="scamdev-matrix-password-toggle"
							data-password-toggle
							aria-controls="mx_password"
							aria-pressed="false"
							aria-label="<?php esc_attr_e( 'Показать пароль', 'scam-dev-matrix' ); ?>">
							<?php esc_html_e( 'Показать', 'scam-dev-matrix' ); ?>
						</button>
					</div>
				</div>

				<div class="scamdev-matrix-field">
					<label for="mx_password_confirm">
						<?php esc_html_e( 'Подтверждение пароля', 'scam-dev-matrix' ); ?>
					</label>
					<div class="scamdev-matrix-password">
						<input type="password"
							name="mx_password_confirm"
							id="mx_password_confirm"
							required
							minlength="12"
							maxlength="128"
							autocomplete="new-password">
						<button type="button"
							class="scamdev-matrix-password-toggle"
							data-password-toggle
							aria-controls="mx_password_confirm"
							aria-pressed="false"
							aria-label="<?php esc_attr_e( 'Показать пароль', 'scam-dev-matrix' ); ?>">
							<?php esc_html_e( 'Показать', 'scam-dev-matrix' ); ?>
						</button>
					</div>
				</div>

				<?php if ( Scam_Dev_Matrix_Registration_Service::invite_is_configured() ) : ?>
					<div class="scamdev-matrix-field">
						<label for="mx_invite_code">
							<?php esc_html_e( 'Код приглашения', 'scam-dev-matrix' ); ?>
						</label>
						<input type="password"
							name="mx_invite_code"
							id="mx_invite_code"
							required
							maxlength="128"
							autocomplete="one-time-code">
					</div>
				<?php endif; ?>

				<?php if ( Scam_Dev_Matrix_Registration_Service::turnstile_is_configured() ) : ?>
					<div class="cf-turnstile"
						data-sitekey="<?php echo esc_attr( Scam_Dev_Matrix_Registration_Service::get_turnstile_site_key() ); ?>"></div>
				<?php endif; ?>

				<label class="scamdev-matrix-consent" for="mx_tos">
					<input type="checkbox"
						name="mx_tos"
						id="mx_tos"
						value="1"
						required>
					<span>
						<?php esc_html_e( 'Я принимаю', 'scam-dev-matrix' ); ?>
						<a href="<?php echo esc_url( home_url( '/tos/' ) ); ?>"
							target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'условия использования', 'scam-dev-matrix' ); ?>
						</a>
						<?php esc_html_e( 'и', 'scam-dev-matrix' ); ?>
						<a href="<?php echo esc_url( get_privacy_policy_url() ?: home_url( '/privacy-policy/' ) ); ?>"
							target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'политику конфиденциальности', 'scam-dev-matrix' ); ?>
						</a>.
					</span>
				</label>

				<button type="submit"
					name="matrix_register"
					value="1"
					class="scamdev-matrix-button">
					<?php esc_html_e( 'Зарегистрироваться', 'scam-dev-matrix' ); ?>
				</button>

				<p class="scamdev-matrix-help">
					<?php
					esc_html_e(
						'Пароль передаётся напрямую Matrix-серверу по HTTPS и не сохраняется в WordPress.',
						'scam-dev-matrix'
					);
					?>
				</p>
			</form>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
