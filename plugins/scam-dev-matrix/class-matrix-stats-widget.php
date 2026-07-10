<?php
/**
 * Cached Matrix status widget.
 *
 * @package Scam_Dev_Matrix
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Matrix status widget.
 */
class Scam_Dev_Matrix_Status_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'scamdev_matrix_stats',
			__( 'Matrix Status', 'scam-dev-matrix' ),
			array(
				'description' => __( 'Кешируемое состояние Matrix-сервера.', 'scam-dev-matrix' ),
			)
		);
	}

	/**
	 * Render the widget.
	 *
	 * @param array $args Widget wrapper args.
	 * @param array $instance Widget settings.
	 */
	public function widget( $args, $instance ) {
		unset( $instance );

		$stats = $this->get_stats();

		echo wp_kses_post( $args['before_widget'] );
		?>
		<div class="scamdev-matrix-status">
			<div class="scamdev-matrix-status__header">
				<span aria-hidden="true">●</span>
				<strong>Matrix</strong>
			</div>
			<?php if ( ! empty( $stats['online'] ) ) : ?>
				<p class="scamdev-matrix-status__online">
					<?php esc_html_e( 'Сервер онлайн', 'scam-dev-matrix' ); ?>
				</p>
				<?php if ( isset( $stats['rooms'] ) ) : ?>
					<p class="scamdev-matrix-status__meta">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %d: room count. */
								__( 'Комнат: %d', 'scam-dev-matrix' ),
								(int) $stats['rooms']
							)
						);
						?>
					</p>
				<?php endif; ?>
			<?php else : ?>
				<p class="scamdev-matrix-status__offline">
					<?php esc_html_e( 'Статус временно недоступен', 'scam-dev-matrix' ); ?>
				</p>
			<?php endif; ?>
		</div>
		<?php
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Return cached server stats.
	 *
	 * @return array
	 */
	private function get_stats() {
		$cached = get_transient( 'scam_dev_matrix_stats_v2' );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		$home = defined( 'MATRIX_HOMESERVER_URL' )
			? untrailingslashit( (string) MATRIX_HOMESERVER_URL )
			: '';
		$token = defined( 'MATRIX_ADMIN_TOKEN' )
			? trim( (string) MATRIX_ADMIN_TOKEN )
			: '';
		$host          = wp_parse_url( $home, PHP_URL_HOST );
		$expected_host = defined( 'MATRIX_EXPECTED_HOST' )
			? strtolower( trim( (string) MATRIX_EXPECTED_HOST ) )
			: '';

		if ( ! $token
			|| 'https' !== wp_parse_url( $home, PHP_URL_SCHEME )
			|| ! $host
			|| ! $expected_host
			|| $expected_host !== strtolower( $host )
		) {
			return array( 'online' => false );
		}

		$version_response = $this->request(
			$home . '/_synapse/admin/v1/server_version',
			$token
		);

		if ( is_wp_error( $version_response ) ) {
			$stats = array( 'online' => false );
			set_transient( 'scam_dev_matrix_stats_v2', $stats, MINUTE_IN_SECONDS );
			return $stats;
		}

		$rooms_response = $this->request(
			$home . '/_synapse/admin/v1/rooms?limit=1',
			$token
		);

		$stats = array(
			'online' => true,
		);

		if ( ! is_wp_error( $rooms_response )
			&& isset( $rooms_response['total_rooms'] )
		) {
			$stats['rooms'] = max( 0, (int) $rooms_response['total_rooms'] );
		}

		set_transient(
			'scam_dev_matrix_stats_v2',
			$stats,
			10 * MINUTE_IN_SECONDS
		);

		return $stats;
	}

	/**
	 * Perform a strict Admin API GET.
	 *
	 * @param string $url Endpoint.
	 * @param string $token Admin token.
	 * @return array|WP_Error
	 */
	private function request( $url, $token ) {
		$response = wp_safe_remote_get(
			$url,
			array(
				'headers'     => array(
					'Authorization' => 'Bearer ' . $token,
					'Accept'        => 'application/json',
				),
				'timeout'     => 3,
				'redirection' => 0,
			)
		);

		if ( is_wp_error( $response )
			|| 200 !== wp_remote_retrieve_response_code( $response )
		) {
			return new WP_Error( 'matrix_stats_unavailable' );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return is_array( $body )
			? $body
			: new WP_Error( 'matrix_stats_json' );
	}
}

/**
 * Register the status widget.
 */
function scam_dev_matrix_register_stats_widget() {
	register_widget( 'Scam_Dev_Matrix_Status_Widget' );
}
add_action( 'widgets_init', 'scam_dev_matrix_register_stats_widget' );
