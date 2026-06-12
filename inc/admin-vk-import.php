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
    $result  = null;
    $message = '';

    if ( isset( $_POST['vk_import_nonce'] ) && wp_verify_nonce( $_POST['vk_import_nonce'], 'vk_import_run' ) ) {
        require_once get_template_directory() . '/inc/class-vk-import.php';

        $token    = defined( 'VK_API_TOKEN' ) ? VK_API_TOKEN : '';
        $owner_id = isset( $_POST['vk_owner_id'] ) ? intval( $_POST['vk_owner_id'] ) : 0;
        $count    = isset( $_POST['vk_count'] ) ? intval( $_POST['vk_count'] ) : 10;

        if ( empty( $token ) || empty( $owner_id ) ) {
            $message = '<div class="notice notice-error"><p>Токен или ID владельца не указаны.</p></div>';
        } else {
            $importer = new Scam_Dev_VK_Import( $token, $owner_id );
            $result   = $importer->run( $count );
            if ( isset( $result['error'] ) ) {
                $message = '<div class="notice notice-error"><p>Ошибка: ' . esc_html( $result['error'] ) . '</p></div>';
            } else {
                $message  = sprintf(
                    '<div class="notice notice-success"><p>Импортировано: %d, пропущено: %d, ошибок: %d</p></div>',
                    isset( $result['imported'] ) ? $result['imported'] : 0,
                    isset( $result['skipped'] ) ? $result['skipped'] : 0,
                    isset( $result['errors'] ) ? count( $result['errors'] ) : 0
                );
            }
        }
    }

    $token    = defined( 'VK_API_TOKEN' ) ? substr( VK_API_TOKEN, 0, 20 ) . '...' : 'не задан';
    $owner_id = get_option( 'vk_owner_id', '-227567126' );
    ?>
    <div class="wrap">
        <h1>Импорт постов из VK</h1>
        <?php echo $message; ?>
        <form method="post" class="card" style="max-width:500px;padding:20px;background:#fff;border:1px solid #ccd0d4;border-radius:4px;">
            <?php wp_nonce_field( 'vk_import_run', 'vk_import_nonce' ); ?>
            <p>Токен: <code><?php echo esc_html( $token ); ?></code></p>
            <p>
                <label>Owner ID (со знаком минус для группы):<br>
                <input type="text" name="vk_owner_id" value="<?php echo esc_attr( $owner_id ); ?>" class="regular-text"></label>
            </p>
            <p>
                <label>Количество постов для импорта:<br>
                <input type="number" name="vk_count" value="10" min="1" max="100" class="small-text"></label>
            </p>
            <p><button type="submit" class="button button-primary">Импортировать</button></p>
        </form>

        <?php if ( $result && isset( $result['errors'] ) && ! empty( $result['errors'] ) ) : ?>
            <h3>Ошибки:</h3>
            <ul style="color:#dc3232;">
                <?php foreach ( $result['errors'] as $err ) : ?>
                    <li><?php echo esc_html( $err ); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php
}
