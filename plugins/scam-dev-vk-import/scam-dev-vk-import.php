<?php
/**
 * Plugin Name: Scam Dev VK Import
 * Description: Фоновый и ограниченный по ресурсам импорт публикаций из VK в WordPress.
 * Version: 1.3.0
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Author: Scam Dev
 * License: GPL v2 or later
 * Text Domain: scam-dev-vk-import
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SCAM_DEV_VK_IMPORT_VERSION', '1.3.0' );
define( 'SCAM_DEV_VK_JOB_INDEX', 'scam_dev_vk_jobs' );

require_once __DIR__ . '/class-vk-import.php';

/**
 * Register import admin page.
 */
function scam_dev_vk_import_admin_menu() {
	add_submenu_page(
		'edit.php',
		__( 'Импорт из VK', 'scam-dev-vk-import' ),
		__( 'Импорт из VK', 'scam-dev-vk-import' ),
		'manage_options',
		'vk-import',
		'scam_dev_vk_import_admin_page'
	);
}
add_action( 'admin_menu', 'scam_dev_vk_import_admin_menu' );

/**
 * Return a job option name.
 *
 * @param string $job_id Job ID.
 * @return string
 */
function scam_dev_vk_job_option( $job_id ) {
	return 'scam_dev_vk_job_' . sanitize_key( $job_id );
}

/**
 * Save a job and keep a small index for the admin screen.
 *
 * @param string $job_id Job ID.
 * @param array  $job Job data.
 */
function scam_dev_vk_save_job( $job_id, $job ) {
	update_option( scam_dev_vk_job_option( $job_id ), $job, false );

	$jobs = get_option( SCAM_DEV_VK_JOB_INDEX, array() );
	$jobs = is_array( $jobs ) ? $jobs : array();
	$jobs = array_values(
		array_unique(
			array_merge( array( $job_id ), $jobs )
		)
	);

	foreach ( array_slice( $jobs, 20 ) as $old_job_id ) {
		delete_option( scam_dev_vk_job_option( $old_job_id ) );
	}

	update_option(
		SCAM_DEV_VK_JOB_INDEX,
		array_slice( $jobs, 0, 20 ),
		false
	);
}

/**
 * Create a background import job from the protected admin form.
 *
 * @return string|WP_Error
 */
function scam_dev_vk_create_job() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return new WP_Error( 'forbidden' );
	}

	check_admin_referer( 'scam_dev_vk_create_job' );

	if ( ! defined( 'VK_API_TOKEN' ) || '' === trim( (string) VK_API_TOKEN ) ) {
		return new WP_Error(
			'missing_token',
			__( 'VK_API_TOKEN не настроен.', 'scam-dev-vk-import' )
		);
	}

	$owner_id = isset( $_POST['vk_owner_id'] )
		? (int) wp_unslash( $_POST['vk_owner_id'] )
		: 0;
	$count = isset( $_POST['vk_count'] )
		? max( 1, min( 50, absint( wp_unslash( $_POST['vk_count'] ) ) ) )
		: 10;
	$status = isset( $_POST['vk_post_status'] )
		? sanitize_key( wp_unslash( $_POST['vk_post_status'] ) )
		: 'draft';
	$category_id = isset( $_POST['vk_category_id'] )
		? absint( wp_unslash( $_POST['vk_category_id'] ) )
		: 0;

	if ( 0 === $owner_id ) {
		return new WP_Error(
			'invalid_owner',
			__( 'Укажите ненулевой owner ID.', 'scam-dev-vk-import' )
		);
	}

	if ( ! in_array( $status, array( 'draft', 'pending', 'publish' ), true ) ) {
		$status = 'draft';
	}

	if ( $category_id ) {
		$category = term_exists( $category_id, 'category' );

		if ( is_wp_error( $category ) || ! $category ) {
			return new WP_Error(
				'invalid_category',
				__( 'Указанная рубрика не существует.', 'scam-dev-vk-import' )
			);
		}
	}

	update_option( 'scam_dev_vk_owner_id', $owner_id, false );

	$job_id = wp_generate_uuid4();
	$job    = array(
		'id'          => $job_id,
		'owner_id'    => $owner_id,
		'requested'   => $count,
		'offset'      => 0,
		'batch_size'  => 5,
		'post_status' => $status,
		'category_id' => $category_id,
		'status'      => 'queued',
		'imported'    => 0,
		'skipped'     => 0,
		'errors'      => array(),
		'created_at'  => gmdate( 'c' ),
		'updated_at'  => gmdate( 'c' ),
	);

	scam_dev_vk_save_job( $job_id, $job );

	if ( ! wp_next_scheduled( 'scam_dev_vk_process_job', array( $job_id ) ) ) {
		$scheduled = wp_schedule_single_event(
			time() + 1,
			'scam_dev_vk_process_job',
			array( $job_id ),
			true
		);

		if ( is_wp_error( $scheduled ) || ! $scheduled ) {
			$job['status']       = 'failed';
			$job['updated_at']   = gmdate( 'c' );
			$job['errors'][]     = __(
				'WordPress Cron не смог запланировать импорт.',
				'scam-dev-vk-import'
			);
			scam_dev_vk_save_job( $job_id, $job );

			return new WP_Error(
				'cron_schedule_failed',
				$job['errors'][0]
			);
		}
	}

	return $job_id;
}

/**
 * Process one import job batch.
 *
 * @param string $job_id Job ID.
 */
function scam_dev_vk_process_job( $job_id ) {
	$job_id = sanitize_key( $job_id );
	$job    = get_option( scam_dev_vk_job_option( $job_id ) );

	if ( ! is_array( $job )
		|| in_array( $job['status'], array( 'complete', 'failed' ), true )
	) {
		return;
	}

	$lock_key      = 'scam_dev_vk_lock_' . $job_id;
	$existing_lock = get_option( $lock_key, false );

	if ( false !== $existing_lock ) {
		if ( is_numeric( $existing_lock )
			&& ( time() - (int) $existing_lock ) > 15 * MINUTE_IN_SECONDS
		) {
			delete_option( $lock_key );
		} else {
			return;
		}
	}

	if ( ! add_option( $lock_key, time(), '', false ) ) {
		return;
	}

	try {
		if ( ! defined( 'VK_API_TOKEN' )
			|| '' === trim( (string) VK_API_TOKEN )
		) {
			$job['status']   = 'failed';
			$job['errors'][] = __( 'VK_API_TOKEN не настроен.', 'scam-dev-vk-import' );
			$job['updated_at'] = gmdate( 'c' );
			scam_dev_vk_save_job( $job_id, $job );
			return;
		}

		$remaining = max( 0, (int) $job['requested'] - (int) $job['offset'] );
		$batch     = min( (int) $job['batch_size'], $remaining );

		if ( $batch < 1 ) {
			$job['status'] = 'complete';
			$job['updated_at'] = gmdate( 'c' );
			scam_dev_vk_save_job( $job_id, $job );
			return;
		}

		$job['status'] = 'processing';
		$job['updated_at'] = gmdate( 'c' );
		scam_dev_vk_save_job( $job_id, $job );

		$importer = new Scam_Dev_VK_Importer(
			(string) VK_API_TOKEN,
			(int) $job['owner_id'],
			(string) $job['post_status'],
			(int) $job['category_id']
		);
		$result = $importer->run( $batch, (int) $job['offset'] );

		if ( is_wp_error( $result ) ) {
			$job['status']   = 'failed';
			$job['errors'][] = $result->get_error_message();
			$job['updated_at'] = gmdate( 'c' );
			scam_dev_vk_save_job( $job_id, $job );
			return;
		}

		$processed       = max( 0, (int) $result['processed'] );
		$job['offset']  += $processed;
		$job['imported'] += (int) $result['imported'];
		$job['skipped'] += (int) $result['skipped'];
		$job['errors']   = array_slice(
			array_merge( $job['errors'], $result['errors'] ),
			-50
		);
		$job['updated_at'] = gmdate( 'c' );

		if ( $processed < $batch || $job['offset'] >= $job['requested'] ) {
			$job['status'] = 'complete';
		} else {
			$job['status'] = 'queued';
			$scheduled     = wp_schedule_single_event(
				time() + 10,
				'scam_dev_vk_process_job',
				array( $job_id ),
				true
			);

			if ( is_wp_error( $scheduled ) || ! $scheduled ) {
				$job['status']   = 'failed';
				$job['errors'][] = __(
					'Не удалось запланировать следующий пакет импорта.',
					'scam-dev-vk-import'
				);
			}
		}

		scam_dev_vk_save_job( $job_id, $job );
	} finally {
		delete_option( $lock_key );
	}
}
add_action( 'scam_dev_vk_process_job', 'scam_dev_vk_process_job' );

/**
 * Render the admin page.
 */
function scam_dev_vk_import_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die(
			esc_html__( 'Недостаточно прав.', 'scam-dev-vk-import' ),
			'',
			array( 'response' => 403 )
		);
	}

	$notice = '';

	$submitted = isset( $_POST['scam_dev_vk_submit'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
		? sanitize_text_field( wp_unslash( $_POST['scam_dev_vk_submit'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
		: '';

	if ( '1' === $submitted ) {
		$result = scam_dev_vk_create_job();

		if ( is_wp_error( $result ) ) {
			$notice = '<div class="notice notice-error"><p>'
				. esc_html( $result->get_error_message() )
				. '</p></div>';
		} else {
			$notice = '<div class="notice notice-success"><p>'
				. esc_html__(
					'Задача создана. WordPress Cron обработает импорт небольшими пакетами.',
					'scam-dev-vk-import'
				)
				. '</p></div>';
		}
	}

	$owner_id = (int) get_option( 'scam_dev_vk_owner_id', -134499579 );
	$job_ids  = get_option( SCAM_DEV_VK_JOB_INDEX, array() );
	$job_ids  = is_array( $job_ids ) ? $job_ids : array();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Импорт публикаций из VK', 'scam-dev-vk-import' ); ?></h1>
		<?php echo wp_kses_post( $notice ); ?>

		<p>
			<strong><?php esc_html_e( 'API token:', 'scam-dev-vk-import' ); ?></strong>
			<?php
			echo defined( 'VK_API_TOKEN' ) && VK_API_TOKEN
				? esc_html__( 'настроен', 'scam-dev-vk-import' )
				: esc_html__( 'не настроен', 'scam-dev-vk-import' );
			?>
		</p>

		<form method="post" class="card" style="max-width: 42rem; padding: 1.25rem;">
			<?php wp_nonce_field( 'scam_dev_vk_create_job' ); ?>

			<p>
				<label for="vk_owner_id">
					<?php esc_html_e( 'Owner ID:', 'scam-dev-vk-import' ); ?>
				</label><br>
				<input type="number"
					name="vk_owner_id"
					id="vk_owner_id"
					value="<?php echo esc_attr( $owner_id ); ?>"
					class="regular-text"
					required>
			</p>

			<p>
				<label for="vk_count">
					<?php esc_html_e( 'Количество публикаций (1–50):', 'scam-dev-vk-import' ); ?>
				</label><br>
				<input type="number"
					name="vk_count"
					id="vk_count"
					value="10"
					min="1"
					max="50"
					class="small-text">
			</p>

			<p>
				<label for="vk_post_status">
					<?php esc_html_e( 'Статус создаваемых записей:', 'scam-dev-vk-import' ); ?>
				</label><br>
				<select name="vk_post_status" id="vk_post_status">
					<option value="draft"><?php esc_html_e( 'Черновик', 'scam-dev-vk-import' ); ?></option>
					<option value="pending"><?php esc_html_e( 'На утверждении', 'scam-dev-vk-import' ); ?></option>
					<option value="publish"><?php esc_html_e( 'Опубликовать после успешного импорта', 'scam-dev-vk-import' ); ?></option>
				</select>
			</p>

			<p>
				<label for="vk_category_id">
					<?php esc_html_e( 'Категория:', 'scam-dev-vk-import' ); ?>
				</label><br>
				<?php
				wp_dropdown_categories(
					array(
						'name'             => 'vk_category_id',
						'id'               => 'vk_category_id',
						'show_option_none' => __( 'Без категории', 'scam-dev-vk-import' ),
						'option_none_value' => 0,
						'hide_empty'       => false,
					)
				);
				?>
			</p>

			<p>
				<button type="submit"
					name="scam_dev_vk_submit"
					value="1"
					class="button button-primary">
					<?php esc_html_e( 'Поставить в очередь', 'scam-dev-vk-import' ); ?>
				</button>
			</p>
		</form>

		<h2><?php esc_html_e( 'Последние задачи', 'scam-dev-vk-import' ); ?></h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Создана', 'scam-dev-vk-import' ); ?></th>
					<th><?php esc_html_e( 'Статус', 'scam-dev-vk-import' ); ?></th>
					<th><?php esc_html_e( 'Обработано', 'scam-dev-vk-import' ); ?></th>
					<th><?php esc_html_e( 'Импортировано', 'scam-dev-vk-import' ); ?></th>
					<th><?php esc_html_e( 'Пропущено', 'scam-dev-vk-import' ); ?></th>
					<th><?php esc_html_e( 'Ошибки', 'scam-dev-vk-import' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! $job_ids ) : ?>
					<tr><td colspan="6"><?php esc_html_e( 'Задач пока нет.', 'scam-dev-vk-import' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $job_ids as $job_id ) : ?>
						<?php $job = get_option( scam_dev_vk_job_option( $job_id ) ); ?>
						<?php if ( ! is_array( $job ) ) { continue; } ?>
						<tr>
							<td><?php echo esc_html( $job['created_at'] ); ?></td>
							<td><?php echo esc_html( $job['status'] ); ?></td>
							<td><?php echo esc_html( (int) $job['offset'] . ' / ' . (int) $job['requested'] ); ?></td>
							<td><?php echo esc_html( (int) $job['imported'] ); ?></td>
							<td><?php echo esc_html( (int) $job['skipped'] ); ?></td>
							<td>
								<?php
								echo esc_html(
									implode(
										'; ',
										array_slice( (array) $job['errors'], -3 )
									)
								);
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}
