<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Scam_Dev_Donate_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'scamdev_donate',
            'Поддержать проект',
            array( 'description' => 'Кнопка и QR-код для доната через Т-Банк' )
        );
    }

    public function widget( $args, $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : 'Поддержать проект';
        $text  = ! empty( $instance['text'] ) ? $instance['text'] : 'Помогите серверу жить';

        echo $args['before_widget'];
        if ( $title ) {
            echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
        }
        ?>
        <div class="glass" style="padding:1.25rem;text-align:center;">
            <p style="color:var(--color-text-secondary);font-size:0.85rem;margin-bottom:1rem;">
                <?php echo esc_html( $text ); ?>
            </p>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode('https://tbank.ru/cf/2x90jZaUieT'); ?>"
                 alt="QR-код Т-Банк" width="120" height="120"
                 style="display:block;margin:0 auto 1rem;border-radius:8px;background:#fff;padding:8px;">
            <a href="https://tbank.ru/cf/2x90jZaUieT" target="_blank" rel="noopener"
               style="display:inline-flex;align-items:center;justify-content:center;gap:6px;
                      background:var(--color-accent,#ff5743);color:#19253b;font-weight:600;
                      font-size:0.875rem;padding:0.625rem 1.5rem;border-radius:9999px;
                      text-decoration:none;white-space:nowrap;transition:all 0.2s;">
                ♥ Поддержать
            </a>
        </div>
        <?php
        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
        $text  = ! empty( $instance['text'] ) ? $instance['text'] : '';
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Заголовок:</label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
                   value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>">Текст:</label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"
                   name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" type="text"
                   value="<?php echo esc_attr( $text ); ?>">
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = sanitize_text_field( $new_instance['title'] );
        $instance['text']  = sanitize_text_field( $new_instance['text'] );
        return $instance;
    }
}

add_action( 'widgets_init', function () {
    register_widget( 'Scam_Dev_Donate_Widget' );
} );
