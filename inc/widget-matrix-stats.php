<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Scam_Dev_Matrix_Stats_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'scamdev_matrix_stats',
            'Matrix Stats',
            array( 'description' => 'Статистика Matrix-сервера' )
        );
    }

    public function widget( $args, $instance ) {
        echo $args['before_widget'];

        $token = defined( 'MATRIX_ADMIN_TOKEN' ) ? MATRIX_ADMIN_TOKEN : '';
        $ver   = $this->fetch( 'server_version', $token );
        $rooms = $this->fetch( 'rooms?limit=1', $token );
        $version = $ver['server_version'] ?? '';

        if ( ! $version ) {
            echo '<p style="color:var(--color-text-muted);font-size:13px;">Нет связи</p>';
            echo $args['after_widget'];
            return;
        }
        ?>
        <div class="glass" style="padding:1.25rem;">
            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent)" stroke-width="2">
                    <rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/>
                    <circle cx="6" cy="6" r="1" fill="var(--color-accent)"/><circle cx="6" cy="18" r="1" fill="var(--color-accent)"/>
                </svg>
                <span style="font-size:0.95rem;font-weight:600;color:var(--color-text);">Matrix</span>
                <span style="margin-left:auto;width:6px;height:6px;background:#22c55e;border-radius:50%;box-shadow:0 0 6px #22c55e;"></span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                <div>
                    <div style="font-size:0.65rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:0.05em;">Версия</div>
                    <div style="font-size:0.85rem;font-weight:600;color:var(--color-text);"><?php echo esc_html( $version ); ?></div>
                </div>
                <div>
                    <div style="font-size:0.65rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:0.05em;">Комнат</div>
                    <div style="font-size:0.85rem;font-weight:600;color:var(--color-text);"><?php echo isset($rooms['total_rooms']) ? intval($rooms['total_rooms']) : '—'; ?></div>
                </div>
            </div>
        </div>
        <?php
        echo $args['after_widget'];
    }

    private function fetch( $endpoint, $token ) {
        if ( ! $token ) return false;
        $response = wp_remote_get( 'https://your-matrix.domain/_synapse/admin/v1/' . $endpoint, array(
            'headers' => array( 'Authorization' => 'Bearer ' . $token ),
            'timeout' => 8,
        ) );
        if ( is_wp_error( $response ) ) return false;
        return json_decode( wp_remote_retrieve_body( $response ), true );
    }
}

add_action( 'widgets_init', function () {
    register_widget( 'Scam_Dev_Matrix_Stats_Widget' );
} );
