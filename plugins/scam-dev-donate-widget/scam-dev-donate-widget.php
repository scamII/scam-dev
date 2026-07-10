<?php
/**
 * Plugin Name: Scam Dev Donate Widget
 * Description: Локальный виджет и shortcode для поддержки проекта через Т-Банк.
 * Version: 1.3.0
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Author: Scam Dev
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: scam-dev-donate-widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SCAM_DEV_DONATE_VERSION', '1.3.0' );
define( 'SCAM_DEV_DONATE_URL', 'https://tbank.ru/cf/2x90jZaUieT' );

/**
 * Enqueue the small, theme-independent stylesheet.
 */
function scam_dev_donate_enqueue_assets() {
	$path = plugin_dir_path( __FILE__ ) . 'assets/donate.css';

	wp_enqueue_style(
		'scam-dev-donate',
		plugins_url( 'assets/donate.css', __FILE__ ),
		array(),
		is_readable( $path ) ? filemtime( $path ) : SCAM_DEV_DONATE_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'scam_dev_donate_enqueue_assets' );

/**
 * Render a donation block.
 *
 * @param bool $widget Whether the block is used in a widget.
 * @param string $text Optional copy.
 * @return string
 */
function scam_dev_donate_render( $widget = false, $text = '' ) {
	$text = $text ?: __( 'Проект существует благодаря сообществу. Помогите серверу жить.', 'scam-dev-donate-widget' );

	ob_start();
	?>
	<div class="scamdev-donate<?php echo $widget ? ' scamdev-donate--widget' : ''; ?>">
		<?php if ( $widget ) : ?>
			<img src="<?php echo esc_url( plugins_url( 'assets/tbank-qr.svg', __FILE__ ) ); ?>"
				class="scamdev-donate__qr"
				alt="<?php esc_attr_e( 'QR-код для поддержки через Т-Банк', 'scam-dev-donate-widget' ); ?>"
				width="130" height="130" loading="lazy">
		<?php endif; ?>

		<p class="scamdev-donate__copy">
			<?php echo esc_html( $text ); ?>
		</p>

		<a href="<?php echo esc_url( SCAM_DEV_DONATE_URL ); ?>"
			class="scamdev-donate__button"
			target="_blank"
			rel="noopener noreferrer">
			<span aria-hidden="true">♥</span>
			<?php esc_html_e( 'Поддержать', 'scam-dev-donate-widget' ); ?>
		</a>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Inline donation shortcode.
 *
 * @param array $attributes Shortcode attributes.
 * @return string
 */
function scam_dev_donate_shortcode( $attributes ) {
	$attributes = shortcode_atts(
		array(
			'text' => '',
		),
		$attributes,
		'scamdev_donate_inline'
	);

	return scam_dev_donate_render(
		false,
		sanitize_text_field( $attributes['text'] )
	);
}
add_shortcode( 'scamdev_donate_inline', 'scam_dev_donate_shortcode' );

/**
 * Donation widget.
 */
class Scam_Dev_Donate_Project_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'scamdev_donate',
			__( 'Поддержать проект', 'scam-dev-donate-widget' ),
			array(
				'description' => __( 'Локальный QR-код и ссылка для поддержки.', 'scam-dev-donate-widget' ),
			)
		);
	}

	/**
	 * Render widget.
	 *
	 * @param array $args Widget wrapper arguments.
	 * @param array $instance Widget settings.
	 */
	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] )
			? $instance['title']
			: __( 'Поддержать проект', 'scam-dev-donate-widget' );
		$text  = ! empty( $instance['text'] )
			? $instance['text']
			: __( 'Помогите серверу жить.', 'scam-dev-donate-widget' );

		echo wp_kses_post( $args['before_widget'] );

		if ( $title ) {
			echo wp_kses_post( $args['before_title'] );
			echo esc_html( $title );
			echo wp_kses_post( $args['after_title'] );
		}

		echo scam_dev_donate_render( true, $text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Render admin form.
	 *
	 * @param array $instance Widget settings.
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$text  = isset( $instance['text'] ) ? $instance['text'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Заголовок:', 'scam-dev-donate-widget' ); ?>
			</label>
			<input class="widefat"
				id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				type="text"
				value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>">
				<?php esc_html_e( 'Текст:', 'scam-dev-donate-widget' ); ?>
			</label>
			<input class="widefat"
				id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>"
				type="text"
				value="<?php echo esc_attr( $text ); ?>">
		</p>
		<?php
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $new_instance New settings.
	 * @param array $old_instance Previous settings.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		unset( $old_instance );

		return array(
			'title' => isset( $new_instance['title'] )
				? sanitize_text_field( $new_instance['title'] )
				: '',
			'text'  => isset( $new_instance['text'] )
				? sanitize_text_field( $new_instance['text'] )
				: '',
		);
	}
}

/**
 * Register widget.
 */
function scam_dev_donate_register_widget() {
	register_widget( 'Scam_Dev_Donate_Project_Widget' );
}
add_action( 'widgets_init', 'scam_dev_donate_register_widget' );
