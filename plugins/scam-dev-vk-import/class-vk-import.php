<?php
/**
 * VK API client and transactional WordPress importer.
 *
 * @package Scam_Dev_VK_Importer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import a bounded VK wall batch.
 */
final class Scam_Dev_VK_Importer {

	/**
	 * Maximum accepted image size.
	 */
	private const MAX_IMAGE_BYTES = 5242880;

	/** Maximum image download attempts for one post. */
	private const MAX_IMAGES_PER_POST = 10;

	/** Maximum image download attempts for one batch. */
	private const MAX_IMAGES_PER_BATCH = 25;

	/**
	 * VK API token.
	 *
	 * @var string
	 */
	private $token;

	/**
	 * VK owner ID.
	 *
	 * @var int
	 */
	private $owner_id;

	/**
	 * Final WordPress post status.
	 *
	 * @var string
	 */
	private $post_status;

	/**
	 * Optional category ID.
	 *
	 * @var int
	 */
	private $category_id;

	/**
	 * Image download attempts used by the current batch.
	 *
	 * @var int
	 */
	private $image_attempts = 0;

	/**
	 * Constructor.
	 *
	 * @param string $token VK API token.
	 * @param int    $owner_id VK owner ID.
	 * @param string $post_status Final post status.
	 * @param int    $category_id Optional category.
	 */
	public function __construct(
		$token,
		$owner_id,
		$post_status = 'draft',
		$category_id = 0
	) {
		$this->token       = trim( (string) $token );
		$this->owner_id    = (int) $owner_id;
		$this->post_status = in_array(
			$post_status,
			array( 'draft', 'pending', 'publish' ),
			true
		) ? $post_status : 'draft';
		$this->category_id = absint( $category_id );
	}

	/**
	 * Import one bounded batch.
	 *
	 * @param int $count Batch size.
	 * @param int $offset VK offset.
	 * @return array|WP_Error
	 */
	public function run( $count = 5, $offset = 0 ) {
		$count                = max( 1, min( 5, absint( $count ) ) );
		$offset               = max( 0, absint( $offset ) );
		$this->image_attempts = 0;

		if ( ! $this->token || 0 === $this->owner_id ) {
			return new WP_Error(
				'vk_configuration',
				__( 'VK importer не настроен.', 'scam-dev-vk-import' )
			);
		}

		$response = wp_safe_remote_post(
			'https://api.vk.com/method/wall.get',
			array(
				'timeout'             => 15,
				'redirection'         => 0,
				'limit_response_size' => 2 * MB_IN_BYTES,
				'headers'             => array( 'Accept' => 'application/json' ),
				'body'        => array(
					'owner_id'     => $this->owner_id,
					'count'        => $count,
					'offset'       => $offset,
					'filter'       => 'owner',
					'extended'     => 1,
					'v'            => '5.199',
					'access_token' => $this->token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'vk_network',
				__( 'Не удалось связаться с VK API.', 'scam-dev-vk-import' )
			);
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error(
				'vk_http',
				__( 'VK API вернул неожиданный HTTP-статус.', 'scam-dev-vk-import' )
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $body ) ) {
			return new WP_Error(
				'vk_json',
				__( 'VK API вернул некорректный JSON.', 'scam-dev-vk-import' )
			);
		}

		if ( ! empty( $body['error'] ) ) {
			return new WP_Error(
				'vk_api',
				__( 'VK API отклонил запрос. Проверьте token и owner ID.', 'scam-dev-vk-import' )
			);
		}

		$items = isset( $body['response']['items'] )
			&& is_array( $body['response']['items'] )
			? $body['response']['items']
			: array();

		$result = array(
			'processed' => count( $items ),
			'imported'  => 0,
			'skipped'   => 0,
			'errors'    => array(),
		);

		foreach ( $items as $item ) {
			$import = $this->import_item( $item );

			if ( is_wp_error( $import ) ) {
				$result['errors'][] = $import->get_error_message();
			} elseif ( 'skipped' === $import ) {
				++$result['skipped'];
			} else {
				++$result['imported'];
			}
		}

		return $result;
	}

	/**
	 * Import one VK item.
	 *
	 * @param array $item VK item.
	 * @return int|string|WP_Error
	 */
	private function import_item( $item ) {
		$vk_id = isset( $item['id'] ) ? absint( $item['id'] ) : 0;

		if ( ! $vk_id ) {
			return new WP_Error(
				'vk_invalid_id',
				__( 'VK вернул публикацию без корректного ID.', 'scam-dev-vk-import' )
			);
		}

		$source_key = $this->owner_id . ':' . $vk_id;
		$existing   = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'any',
				'meta_key'       => '_vk_source_key', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => $source_key, // phpcs:ignore WordPress.DB.SlowDBQuery
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);

		if ( $existing ) {
			return 'skipped';
		}

		$text = isset( $item['text'] ) ? trim( (string) $item['text'] ) : '';

		if ( '' === $text && ! empty( $item['attachments'][0]['link']['description'] ) ) {
			$text = (string) $item['attachments'][0]['link']['description'];
		}

		if ( '' === $text && ! empty( $item['attachments'][0]['link']['title'] ) ) {
			$text = (string) $item['attachments'][0]['link']['title'];
		}

		$text = (string) preg_replace(
			'/\[(?:https?:\/\/[^\|\]]+|(?:id|club)\d+)\|([^\]]+)\]/u',
			'$1',
			$text
		);

		$first_line = trim( strtok( $text, "\n" ) ?: '' );
		$title      = sanitize_text_field(
			wp_trim_words(
				$first_line ?: sprintf( 'VK post %d', $vk_id ),
				12,
				''
			)
		);

		$timestamp = isset( $item['date'] ) ? (int) $item['date'] : time();
		$date_gmt   = gmdate( 'Y-m-d H:i:s', $timestamp );
		$date_local = get_date_from_gmt( $date_gmt );

		$post_data = array(
			'post_title'    => $title,
			'post_content'  => '',
			'post_status'   => 'draft',
			'post_type'     => 'post',
			'post_date'     => $date_local,
			'post_date_gmt' => $date_gmt,
			'post_name'     => sanitize_title( $title ),
			'meta_input'    => array(
				'_vk_source_key' => $source_key,
				'_vk_post_id'    => $vk_id,
				'_vk_owner_id'   => $this->owner_id,
			),
		);

		if ( $this->category_id ) {
			$post_data['post_category'] = array( $this->category_id );
		}

		$post_id = wp_insert_post( $post_data, true );

		if ( is_wp_error( $post_id ) ) {
			return new WP_Error(
				'vk_post_insert',
				sprintf(
					/* translators: %d: VK post ID. */
					__( 'Не удалось создать черновик для VK post %d.', 'scam-dev-vk-import' ),
					$vk_id
				)
			);
		}

		$attachment_ids = array();
		$featured_id    = 0;
		$content        = $this->text_to_blocks( $text );
		$warnings           = array();
		$post_image_attempts = 0;
		$budget_warning      = __(
			'Часть изображений пропущена из-за лимита загрузок.',
			'scam-dev-vk-import'
		);

		foreach ( (array) ( $item['attachments'] ?? array() ) as $attachment ) {
			$type = isset( $attachment['type'] )
				? sanitize_key( $attachment['type'] )
				: '';

			if ( 'photo' === $type && ! empty( $attachment['photo']['sizes'] ) ) {
				if ( $post_image_attempts >= self::MAX_IMAGES_PER_POST
					|| $this->image_attempts >= self::MAX_IMAGES_PER_BATCH
				) {
					if ( ! in_array( $budget_warning, $warnings, true ) ) {
						$warnings[] = $budget_warning;
					}
					continue;
				}

				$image_url = $this->largest_photo_url(
					(array) $attachment['photo']['sizes']
				);

				if ( $image_url ) {
					++$post_image_attempts;
					++$this->image_attempts;
					$attachment_id = $this->download_image(
						$image_url,
						$post_id,
						$vk_id
					);

					if ( is_wp_error( $attachment_id ) ) {
						$warnings[] = $attachment_id->get_error_message();
					} else {
						$attachment_ids[] = $attachment_id;
						$featured_id      = $featured_id ?: $attachment_id;
						$image_markup     = wp_get_attachment_image(
							$attachment_id,
							'large',
							false,
							array( 'loading' => 'lazy' )
						);
						$content .= "\n<!-- wp:image -->\n<figure class=\"wp-block-image\">"
							. $image_markup
							. "</figure>\n<!-- /wp:image -->\n";
					}
				}
			}

			if ( 'video' === $type && ! empty( $attachment['video'] ) ) {
				$video_url = $this->video_url( (array) $attachment['video'] );

				if ( $video_url ) {
					$content .= "\n<!-- wp:paragraph --><p><a href=\""
						. esc_url( $video_url )
						. "\" target=\"_blank\" rel=\"noopener noreferrer\">"
						. esc_html__( 'Смотреть видео во ВКонтакте', 'scam-dev-vk-import' )
						. "</a></p><!-- /wp:paragraph -->\n";
				}
			}
		}

		if ( '' === trim( $content ) ) {
			foreach ( $attachment_ids as $attachment_id ) {
				wp_delete_attachment( $attachment_id, true );
			}
			wp_delete_post( $post_id, true );

			return 'skipped';
		}

		$update = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => $content,
				'post_status'  => $this->post_status,
			),
			true
		);

		if ( is_wp_error( $update ) || ! $update ) {
			foreach ( $attachment_ids as $attachment_id ) {
				wp_delete_attachment( $attachment_id, true );
			}
			wp_delete_post( $post_id, true );

			return new WP_Error(
				'vk_post_finalize',
				sprintf(
					/* translators: %d: VK post ID. */
					__( 'Импорт VK post %d отменён при финализации.', 'scam-dev-vk-import' ),
					$vk_id
				)
			);
		}

		if ( $featured_id ) {
			set_post_thumbnail( $post_id, $featured_id );
		}

		if ( $warnings ) {
			update_post_meta(
				$post_id,
				'_vk_import_warnings',
				array_slice( $warnings, 0, 20 )
			);
		}

		return $post_id;
	}

	/**
	 * Convert plain text to paragraph blocks.
	 *
	 * @param string $text Plain text.
	 * @return string
	 */
	private function text_to_blocks( $text ) {
		$blocks = '';

		foreach ( preg_split( '/\R/u', $text ) as $paragraph ) {
			$paragraph = trim( $paragraph );

			if ( '' === $paragraph ) {
				continue;
			}

			$blocks .= '<!-- wp:paragraph --><p>'
				. esc_html( $paragraph )
				. "</p><!-- /wp:paragraph -->\n";
		}

		return $blocks;
	}

	/**
	 * Find the largest image URL.
	 *
	 * @param array $sizes VK photo sizes.
	 * @return string
	 */
	private function largest_photo_url( $sizes ) {
		$largest  = array();
		$max_area = 0;

		foreach ( $sizes as $size ) {
			$width  = isset( $size['width'] ) ? absint( $size['width'] ) : 0;
			$height = isset( $size['height'] ) ? absint( $size['height'] ) : 0;
			$area   = $width * $height;

			if ( $area > $max_area && ! empty( $size['url'] ) ) {
				$max_area = $area;
				$largest  = $size;
			}
		}

		$url = isset( $largest['url'] ) ? esc_url_raw( $largest['url'] ) : '';

		return $this->is_allowed_media_url( $url ) ? $url : '';
	}

	/**
	 * Validate an external VK media URL.
	 *
	 * @param string $url URL.
	 * @return bool
	 */
	private function is_allowed_media_url( $url ) {
		if ( ! wp_http_validate_url( $url )
			|| 'https' !== wp_parse_url( $url, PHP_URL_SCHEME )
		) {
			return false;
		}

		$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
		$allowed_suffixes = array(
			'userapi.com',
			'vkuserphoto.ru',
			'vk-cdn.net',
		);

		foreach ( $allowed_suffixes as $suffix ) {
			if ( $host === $suffix || str_ends_with( $host, '.' . $suffix ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Safely download and sideload a bounded image.
	 *
	 * @param string $url Image URL.
	 * @param int    $post_id Parent post.
	 * @param int    $vk_id VK post ID.
	 * @return int|WP_Error
	 */
	private function download_image( $url, $post_id, $vk_id ) {
		if ( ! $this->is_allowed_media_url( $url ) ) {
			return new WP_Error(
				'vk_image_host',
				__( 'Изображение отклонено: недоверенный host.', 'scam-dev-vk-import' )
			);
		}

		$temp_file = wp_tempnam( 'scam-dev-vk-image' );

		if ( ! $temp_file ) {
			return new WP_Error( 'vk_image_temp' );
		}

		$response = wp_safe_remote_get(
			$url,
			array(
				'timeout'             => 15,
				'redirection'         => 0,
				'stream'              => true,
				'filename'            => $temp_file,
				'limit_response_size' => self::MAX_IMAGE_BYTES + 1,
			)
		);

		if ( is_wp_error( $response )
			|| 200 !== wp_remote_retrieve_response_code( $response )
		) {
			wp_delete_file( $temp_file );
			return new WP_Error(
				'vk_image_download',
				__( 'Не удалось скачать изображение.', 'scam-dev-vk-import' )
			);
		}

		$size = filesize( $temp_file );

		if ( false === $size || $size < 1 || $size > self::MAX_IMAGE_BYTES ) {
			wp_delete_file( $temp_file );
			return new WP_Error(
				'vk_image_size',
				__( 'Изображение превышает лимит 5 МБ или пусто.', 'scam-dev-vk-import' )
			);
		}

		$image_info = getimagesize( $temp_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		if ( ! is_array( $image_info ) || empty( $image_info['mime'] ) ) {
			wp_delete_file( $temp_file );
			return new WP_Error(
				'vk_image_invalid',
				__( 'Скачанный файл не является изображением.', 'scam-dev-vk-import' )
			);
		}

		$width  = isset( $image_info[0] ) ? absint( $image_info[0] ) : 0;
		$height = isset( $image_info[1] ) ? absint( $image_info[1] ) : 0;
		$pixels = $width * $height;

		if ( $width < 1
			|| $height < 1
			|| $width > 12000
			|| $height > 12000
			|| $pixels > 40000000
		) {
			wp_delete_file( $temp_file );
			return new WP_Error(
				'vk_image_dimensions',
				__( 'Размеры изображения превышают безопасный лимит.', 'scam-dev-vk-import' )
			);
		}

		$extensions = array(
			'image/jpeg' => 'jpg',
			'image/png'  => 'png',
			'image/gif'  => 'gif',
			'image/webp' => 'webp',
		);
		$mime       = strtolower( (string) $image_info['mime'] );

		if ( ! isset( $extensions[ $mime ] ) ) {
			wp_delete_file( $temp_file );
			return new WP_Error(
				'vk_image_mime',
				__( 'Тип изображения не разрешён.', 'scam-dev-vk-import' )
			);
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$file = array(
			'name'     => sanitize_file_name(
				sprintf( 'vk-%d-%s.%s', $vk_id, wp_generate_password( 8, false ), $extensions[ $mime ] )
			),
			'tmp_name' => $temp_file,
			'type'     => $mime,
			'error'    => 0,
			'size'     => $size,
		);

		$attachment_id = media_handle_sideload(
			$file,
			$post_id,
			null,
			array( 'post_status' => 'inherit' )
		);

		if ( is_wp_error( $attachment_id ) ) {
			if ( file_exists( $temp_file ) ) {
				wp_delete_file( $temp_file );
			}
			return new WP_Error(
				'vk_image_sideload',
				__( 'WordPress не смог сохранить изображение.', 'scam-dev-vk-import' )
			);
		}

		return $attachment_id;
	}

	/**
	 * Build a safe public VK video URL.
	 *
	 * @param array $video VK video.
	 * @return string
	 */
	private function video_url( $video ) {
		$owner_id   = isset( $video['owner_id'] ) ? (int) $video['owner_id'] : 0;
		$video_id  = isset( $video['id'] ) ? absint( $video['id'] ) : 0;
		$access_key = isset( $video['access_key'] )
			? sanitize_text_field( $video['access_key'] )
			: '';

		if ( ! $owner_id || ! $video_id ) {
			return '';
		}

		$url = 'https://vk.com/video' . $owner_id . '_' . $video_id;

		if ( $access_key ) {
			$url = add_query_arg( 'access_key', $access_key, $url );
		}

		return esc_url_raw( $url );
	}
}
