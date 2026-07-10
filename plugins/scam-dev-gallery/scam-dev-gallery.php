<?php
/**
 * Plugin Name: Scam Dev Gallery
 * Description: Галереи изображений с безопасным shortcode, Gutenberg-блоком и доступным lightbox.
 * Version: 1.3.0
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Author: Scam Dev
 * License: GPL v2 or later
 * Text Domain: scam-dev-gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SCAMDEV_GALLERY_VERSION', '1.3.0' );
define( 'SCAMDEV_GALLERY_FILE', __FILE__ );

/**
 * Register the gallery post type and block.
 */
function scamdev_gallery_init() {
	register_post_type(
		'scamdev_gallery',
		array(
			'labels' => array(
				'name'          => __( 'Галереи', 'scam-dev-gallery' ),
				'singular_name' => __( 'Галерея', 'scam-dev-gallery' ),
				'add_new_item'  => __( 'Новая галерея', 'scam-dev-gallery' ),
				'edit_item'     => __( 'Редактировать галерею', 'scam-dev-gallery' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'menu_icon'          => 'dashicons-format-gallery',
			'supports'           => array( 'title' ),
			'menu_position'      => 21,
		)
	);

	register_block_type( __DIR__ . '/block.json' );
}
add_action( 'init', 'scamdev_gallery_init' );

/**
 * Add the image-selection meta box.
 */
function scamdev_gallery_add_meta_box() {
	add_meta_box(
		'scamdev_gallery_images',
		__( 'Изображения', 'scam-dev-gallery' ),
		'scamdev_gallery_render_meta_box',
		'scamdev_gallery',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_scamdev_gallery', 'scamdev_gallery_add_meta_box' );

/**
 * Render gallery meta box.
 *
 * @param WP_Post $post Gallery post.
 */
function scamdev_gallery_render_meta_box( $post ) {
	$image_ids = scamdev_gallery_get_image_ids( $post->ID );
	wp_nonce_field( 'scamdev_gallery_save', 'scamdev_gallery_nonce' );
	?>
	<div id="scamdev-gallery-admin">
		<input type="hidden"
			name="scamdev_gallery_ids"
			id="scamdev-gallery-ids"
			value="<?php echo esc_attr( implode( ',', $image_ids ) ); ?>">

		<div id="scamdev-gallery-preview" class="scamdev-gallery-admin-preview">
			<?php foreach ( $image_ids as $image_id ) : ?>
				<?php
				$thumbnail = wp_get_attachment_image_url( $image_id, 'thumbnail' );
				if ( ! $thumbnail ) {
					continue;
				}
				?>
				<div class="scamdev-gallery-admin-item" data-id="<?php echo esc_attr( $image_id ); ?>">
					<img src="<?php echo esc_url( $thumbnail ); ?>"
						alt=""
						width="100"
						height="100">
					<button type="button"
						class="scamdev-remove-img"
						aria-label="<?php esc_attr_e( 'Удалить изображение', 'scam-dev-gallery' ); ?>">
						&times;
					</button>
				</div>
			<?php endforeach; ?>
		</div>

		<button type="button" class="button" id="scamdev-add-images">
			<?php esc_html_e( 'Выбрать изображения', 'scam-dev-gallery' ); ?>
		</button>
	</div>
	<?php
}

/**
 * Save gallery image IDs.
 *
 * @param int $post_id Gallery post ID.
 */
function scamdev_gallery_save( $post_id ) {
	if ( wp_is_post_revision( $post_id )
		|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
	) {
		return;
	}

	$nonce = isset( $_POST['scamdev_gallery_nonce'] )
		? sanitize_text_field( wp_unslash( $_POST['scamdev_gallery_nonce'] ) )
		: '';

	if ( ! wp_verify_nonce( $nonce, 'scamdev_gallery_save' )
		|| ! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}

	$raw_ids = isset( $_POST['scamdev_gallery_ids'] )
		? sanitize_text_field( wp_unslash( $_POST['scamdev_gallery_ids'] ) )
		: '';

	$image_ids = array_filter(
		array_map( 'absint', explode( ',', $raw_ids ) ),
		static function ( $attachment_id ) {
			return $attachment_id > 0 && wp_attachment_is_image( $attachment_id );
		}
	);

	update_post_meta(
		$post_id,
		'_scamdev_gallery_ids',
		implode( ',', array_values( array_unique( $image_ids ) ) )
	);
}
add_action( 'save_post_scamdev_gallery', 'scamdev_gallery_save' );

/**
 * Enqueue admin assets only on gallery screens.
 */
function scamdev_gallery_admin_assets() {
	$screen = get_current_screen();

	if ( ! $screen || 'scamdev_gallery' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_media();

	$script_path = __DIR__ . '/assets/admin.js';
	$style_path  = __DIR__ . '/assets/admin.css';

	wp_enqueue_script(
		'scamdev-gallery-admin',
		plugins_url( 'assets/admin.js', __FILE__ ),
		array(),
		is_readable( $script_path ) ? filemtime( $script_path ) : SCAMDEV_GALLERY_VERSION,
		true
	);
	wp_localize_script(
		'scamdev-gallery-admin',
		'scamdevGalleryAdmin',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'scamdev_preview' ),
			'labels'  => array(
				'select' => __( 'Добавить', 'scam-dev-gallery' ),
				'title'  => __( 'Выберите изображения', 'scam-dev-gallery' ),
				'remove' => __( 'Удалить изображение', 'scam-dev-gallery' ),
			),
		)
	);

	wp_enqueue_style(
		'scamdev-gallery-admin',
		plugins_url( 'assets/admin.css', __FILE__ ),
		array(),
		is_readable( $style_path ) ? filemtime( $style_path ) : SCAMDEV_GALLERY_VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'scamdev_gallery_admin_assets' );

/**
 * AJAX image-preview data.
 */
function scamdev_gallery_ajax_preview() {
	check_ajax_referer( 'scamdev_preview', 'nonce' );

	if ( ! current_user_can( 'upload_files' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}

	$attachment_id = isset( $_POST['id'] )
		? absint( wp_unslash( $_POST['id'] ) )
		: 0;

	if ( ! $attachment_id || ! wp_attachment_is_image( $attachment_id ) ) {
		wp_send_json_error( array( 'message' => 'invalid_attachment' ), 400 );
	}

	$url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );

	if ( ! $url ) {
		wp_send_json_error( array( 'message' => 'not_found' ), 404 );
	}

	wp_send_json_success(
		array(
			'id'  => $attachment_id,
			'url' => esc_url_raw( $url ),
		)
	);
}
add_action( 'wp_ajax_scamdev_preview_img', 'scamdev_gallery_ajax_preview' );

/**
 * Enqueue theme-independent frontend assets.
 */
function scamdev_gallery_frontend_assets() {
	$style_path = __DIR__ . '/assets/gallery.css';
	$script_path = __DIR__ . '/assets/gallery.js';

	wp_enqueue_style(
		'scamdev-gallery',
		plugins_url( 'assets/gallery.css', __FILE__ ),
		array(),
		is_readable( $style_path ) ? filemtime( $style_path ) : SCAMDEV_GALLERY_VERSION
	);
	wp_enqueue_script(
		'scamdev-gallery',
		plugins_url( 'assets/gallery.js', __FILE__ ),
		array(),
		is_readable( $script_path ) ? filemtime( $script_path ) : SCAMDEV_GALLERY_VERSION,
		true
	);
	wp_script_add_data( 'scamdev-gallery', 'strategy', 'defer' );
}
add_action( 'wp_enqueue_scripts', 'scamdev_gallery_frontend_assets' );

/**
 * Parse and validate gallery image IDs.
 *
 * @param int $gallery_id Gallery post ID.
 * @return int[]
 */
function scamdev_gallery_get_image_ids( $gallery_id ) {
	$value = get_post_meta( $gallery_id, '_scamdev_gallery_ids', true );
	$ids   = is_array( $value ) ? $value : explode( ',', (string) $value );

	return array_values(
		array_filter(
			array_map( 'absint', $ids ),
			static function ( $attachment_id ) {
				return $attachment_id > 0 && wp_attachment_is_image( $attachment_id );
			}
		)
	);
}

/**
 * Return galleries the current visitor is allowed to read.
 *
 * @param int[] $requested_ids Explicit gallery IDs.
 * @return int[]
 */
function scamdev_gallery_get_readable_gallery_ids( $requested_ids = array() ) {
	if ( ! empty( $requested_ids ) ) {
		$readable = array();

		foreach ( array_unique( array_map( 'absint', $requested_ids ) ) as $gallery_id ) {
			$gallery = get_post( $gallery_id );

			if ( ! $gallery || 'scamdev_gallery' !== $gallery->post_type ) {
				continue;
			}

			if ( 'publish' === $gallery->post_status
				|| current_user_can( 'read_post', $gallery_id )
			) {
				$readable[] = $gallery_id;
			}
		}

		return $readable;
	}

	return get_posts(
		array(
			'post_type'      => 'scamdev_gallery',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
}

/**
 * Render one isolated gallery component.
 *
 * @param int[] $gallery_ids Gallery IDs.
 * @param int   $columns     Column count.
 * @return string
 */
function scamdev_gallery_render( $gallery_ids, $columns = 3 ) {
	$gallery_ids = scamdev_gallery_get_readable_gallery_ids( $gallery_ids );
	$columns     = max( 2, min( 6, absint( $columns ) ) );
	$images      = array();

	foreach ( $gallery_ids as $gallery_id ) {
		foreach ( scamdev_gallery_get_image_ids( $gallery_id ) as $attachment_id ) {
			$full = wp_get_attachment_image_url( $attachment_id, 'full' );
			$thumb = wp_get_attachment_image_url( $attachment_id, 'medium_large' );

			if ( ! $full || ! $thumb ) {
				continue;
			}

			$images[] = array(
				'full'  => $full,
				'thumb' => $thumb,
				'alt'   => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
			);
		}
	}

	if ( empty( $images ) ) {
		return '';
	}

	$component_id = wp_unique_id( 'scamdev-gallery-' );
	$dialog_id    = $component_id . '-dialog';

	ob_start();
	?>
	<div id="<?php echo esc_attr( $component_id ); ?>"
		class="scamdev-gallery-component scamdev-gallery-cols-<?php echo esc_attr( $columns ); ?>"
		data-columns="<?php echo esc_attr( $columns ); ?>">
		<div class="scamdev-gallery-grid">
			<?php foreach ( $images as $index => $image ) : ?>
				<button type="button"
					class="scamdev-gallery-item"
					data-index="<?php echo esc_attr( $index ); ?>"
					data-full="<?php echo esc_url( $image['full'] ); ?>"
					data-alt="<?php echo esc_attr( $image['alt'] ); ?>"
					aria-haspopup="dialog"
					aria-controls="<?php echo esc_attr( $dialog_id ); ?>">
					<img src="<?php echo esc_url( $image['thumb'] ); ?>"
						alt="<?php echo esc_attr( $image['alt'] ); ?>"
						loading="lazy">
				</button>
			<?php endforeach; ?>
		</div>

		<div id="<?php echo esc_attr( $dialog_id ); ?>"
			class="scamdev-lightbox"
			role="dialog"
			aria-modal="true"
			aria-label="<?php esc_attr_e( 'Просмотр изображения', 'scam-dev-gallery' ); ?>"
			hidden>
			<div class="scamdev-lightbox-backdrop" data-gallery-close></div>
			<div class="scamdev-lightbox-content" tabindex="-1">
				<button type="button"
					class="scamdev-lightbox-close"
					data-gallery-close
					aria-label="<?php esc_attr_e( 'Закрыть', 'scam-dev-gallery' ); ?>">
					&times;
				</button>
				<button type="button"
					class="scamdev-lightbox-prev"
					data-gallery-prev
					aria-label="<?php esc_attr_e( 'Предыдущее изображение', 'scam-dev-gallery' ); ?>">
					&#8249;
				</button>
				<img src="" alt="" class="scamdev-lightbox-image">
				<button type="button"
					class="scamdev-lightbox-next"
					data-gallery-next
					aria-label="<?php esc_attr_e( 'Следующее изображение', 'scam-dev-gallery' ); ?>">
					&#8250;
				</button>
			</div>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Gallery shortcode.
 *
 * @param array $attributes Shortcode attributes.
 * @return string
 */
function scamdev_gallery_shortcode( $attributes ) {
	$attributes = shortcode_atts(
		array(
			'id'   => '',
			'cols' => 3,
		),
		$attributes,
		'scamdev_gallery'
	);

	$ids = array_filter(
		array_map(
			'absint',
			preg_split( '/\s*,\s*/', (string) $attributes['id'] )
		)
	);

	return scamdev_gallery_render( $ids, absint( $attributes['cols'] ) );
}
add_shortcode( 'scamdev_gallery', 'scamdev_gallery_shortcode' );
