<?php

function scam_dev_vk_import_admin() {
	add_submenu_page(
		'edit.php',
		'Импорт из VK',
		'Импорт из VK',
		'manage_options',
		'vk-import',
		'scam_dev_vk_import_page'
	);
}
add_action( 'admin_menu', 'scam_dev_vk_import_admin' );

function scam_dev_vk_import_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$result  = null;
	$message = '';

	if ( isset( $_POST['vk_import_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vk_import_nonce'] ) ), 'vk_import_run' ) ) {
		require_once get_template_directory() . '/inc/class-vk-import.php';

		$raw_owner = isset( $_POST['vk_owner_id'] ) ? wp_unslash( $_POST['vk_owner_id'] ) : '';
		$owner_id  = (int) $raw_owner;
		$count     = isset( $_POST['vk_count'] ) ? absint( wp_unslash( $_POST['vk_count'] ) ) : 10;
		$count     = max( 1, min( 100, $count ) );

		if ( defined( 'VK_API_TOKEN' ) && VK_API_TOKEN ) {
			$token = VK_API_TOKEN;
		} else {
			$token = '';
		}

		if ( '' === $token || 0 === $owner_id ) {
			$message = '<div class="notice notice-error"><p>' . esc_html__( 'Токен или ID владельца не указаны.', 'scam-dev' ) . '</p></div>';
		} else {
			$importer = new Scam_Dev_VK_Import( $token, $owner_id );
			$result   = $importer->run( $count );
			if ( isset( $result['error'] ) ) {
				$message = '<div class="notice notice-error"><p>' . esc_html( $result['error'] ) . '</p></div>';
			} else {
				$message = '<div class="notice notice-success"><p>' . sprintf(
					esc_html__( 'Импортировано: %d, пропущено: %d, ошибок: %d', 'scam-dev' ),
					isset( $result['imported'] ) ? (int) $result['imported'] : 0,
					isset( $result['skipped'] ) ? (int) $result['skipped'] : 0,
					isset( $result['errors'] ) ? count( $result['errors'] ) : 0
				) . '</p></div>';
			}
		}
	}

	$token    = defined( 'VK_API_TOKEN' ) && VK_API_TOKEN ? substr( VK_API_TOKEN, 0, 20 ) . '...' : 'не задан';
	$owner_id = get_option( 'vk_owner_id', '-134499579' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Импорт постов из VK', 'scam-dev' ); ?></h1>
		<?php echo $message; // WPCS: XSS ok — $message is built with esc_html() above. ?>
		<form method="post" class="card" style="max-width:500px;padding:20px;background:#fff;border:1px solid #ccd0d4;border-radius:4px;">
			<?php wp_nonce_field( 'vk_import_run', 'vk_import_nonce' ); ?>
			<p><?php esc_html_e( 'Токен:', 'scam-dev' ); ?> <code><?php echo esc_html( $token ); ?></code></p>
			<p>
				<label><?php esc_html_e( 'Owner ID (со знаком минус для группы):', 'scam-dev' ); ?><br>
				<input type="text" name="vk_owner_id" value="<?php echo esc_attr( $owner_id ); ?>" class="regular-text"></label>
			</p>
			<p>
				<label><?php esc_html_e( 'Количество постов для импорта:', 'scam-dev' ); ?><br>
				<input type="number" name="vk_count" value="10" min="1" max="100" class="small-text"></label>
			</p>
			<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Импортировать', 'scam-dev' ); ?></button></p>
		</form>

		<?php if ( $result && ! empty( $result['errors'] ) ) : ?>
			<h3><?php esc_html_e( 'Ошибки:', 'scam-dev' ); ?></h3>
			<ul style="color:#dc3232;">
				<?php foreach ( $result['errors'] as $err ) : ?>
					<li><?php echo esc_html( $err ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php
}
