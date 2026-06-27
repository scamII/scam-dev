<?php

class Scam_Dev_VK_Import {

	private $token;
	private $owner_id;

	public function __construct( $token, $owner_id ) {
		$this->token    = $token;
		$this->owner_id = $owner_id;
	}

	public function run( $count = 20, $offset = 0 ) {
		$url = add_query_arg(
			array(
				'owner_id'     => $this->owner_id,
				'count'        => $count,
				'offset'       => $offset,
				'filter'       => 'owner',
				'extended'     => 1,
				'v'            => '5.199',
				'access_token' => $this->token,
			),
			'https://api.vk.com/method/wall.get'
		);

		$response = wp_remote_get( $url, array( 'timeout' => 30 ) );
		if ( is_wp_error( $response ) ) {
			return array(
				'done'  => false,
				'error' => 'HTTP error',
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) ) {
			return array(
				'done'  => false,
				'error' => 'Invalid API response',
			);
		}

		if ( isset( $body['error']['error_msg'] ) ) {
			return array(
				'done'  => false,
				'error' => $body['error']['error_msg'],
			);
		}

		if ( empty( $body['response']['items'] ) || ! is_array( $body['response']['items'] ) ) {
			return array(
				'done'  => false,
				'error' => 'Постов не найдено',
			);
		}

		$items    = $body['response']['items'];
		$imported = 0;
		$skipped  = 0;
		$errors   = array();

		foreach ( $items as $post ) {
			$vk_id   = isset( $post['id'] ) ? (int) $post['id'] : 0;
			$vk_date = isset( $post['date'] ) ? gmdate( 'Y-m-d H:i:s', (int) $post['date'] ) : current_time( 'mysql' );
			$text    = isset( $post['text'] ) ? trim( $post['text'] ) : '';

			if ( empty( $text ) && ! empty( $post['attachments'][0]['link']['description'] ) ) {
				$text = $post['attachments'][0]['link']['description'];
			}
			if ( empty( $text ) && ! empty( $post['attachments'][0]['link']['title'] ) ) {
				$text = $post['attachments'][0]['link']['title'];
			}

			$text = preg_replace( '/\[(?:https?:\/\/[^\|\]]+)\|([^\]]+)\]/u', '$1', $text );
			$text = preg_replace( '/\[(?:id|club)\d+\|([^\]]+)\]/u', '$1', $text );

			if ( empty( $text ) ) {
				++$skipped;
				continue;
			}

			$existing = get_posts(
				array(
					'post_type'      => 'post',
					'post_status'    => 'any',
					'meta_key'       => '_vk_post_id',
					'meta_value'     => $vk_id,
					'posts_per_page' => 1,
				)
			);

			if ( ! empty( $existing ) ) {
				++$skipped;
				continue;
			}

			$image_html  = '';
			$featured_id = 0;
			if ( ! empty( $post['attachments'] ) ) {
				foreach ( $post['attachments'] as $att ) {
					if ( 'photo' === $att['type'] && ! empty( $att['photo']['sizes'] ) ) {
						$sizes    = $att['photo']['sizes'];
						$largest  = $sizes[0];
						$max_area = 0;
						foreach ( $sizes as $sz ) {
							$w    = isset( $sz['width'] ) ? (int) $sz['width'] : 0;
							$h    = isset( $sz['height'] ) ? (int) $sz['height'] : 0;
							$area = $w * $h;
							if ( $area > $max_area ) {
								$max_area = $area;
								$largest  = $sz;
							}
						}
						$img_url = isset( $largest['url'] ) ? $largest['url'] : '';

						if ( $img_url && wp_http_validate_url( $img_url ) ) {
							$thumb_id = $this->upload_image( $img_url, $vk_id );
							if ( $thumb_id && ! $featured_id ) {
								$featured_id = $thumb_id;
							}
							if ( $thumb_id ) {
								$img_src     = wp_get_attachment_url( $thumb_id );
								$image_html .= "\n<!-- wp:image -->\n<figure class=\"wp-block-image\"><img src=\"" . esc_url( $img_src ) . "\" alt=\"\"/></figure>\n<!-- /wp:image -->\n";
							}
						}
					}

					if ( 'video' === $att['type'] && ! empty( $att['video'] ) ) {
						$v    = $att['video'];
						$oid  = isset( $v['owner_id'] ) ? (int) $v['owner_id'] : 0;
						$vid  = isset( $v['id'] ) ? absint( $v['id'] ) : 0;
						$hash = isset( $v['access_key'] ) ? rawurlencode( $v['access_key'] ) : '';

						if ( $oid && $vid && $hash ) {
							$embed       = 'https://vk.com/video_ext.php?oid=' . $oid . '&id=' . $vid . '&hash=' . $hash . '&hd=1';
							$image_html .= "\n<!-- wp:html -->\n";
							$image_html .= '<div class="vk-video-wrapper" style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;max-width:100%;margin:1.5rem 0;">';
							$image_html .= '<iframe src="' . esc_url( $embed ) . '" style="position:absolute;top:0;left:0;width:100%;height:100%;"';
							$image_html .= ' allow="autoplay;encrypted-media;fullscreen;picture-in-picture" frameborder="0" allowfullscreen loading="lazy"></iframe>';
							$image_html .= '</div>';
							$image_html .= "\n<!-- /wp:html -->\n";
						}
					}
				}
			}

			$lines = explode( "\n", $text );
			$title = wp_trim_words( $lines[0], 12, '' );
			$slug  = sanitize_title( $title );
			if ( mb_strlen( $slug ) > 80 ) {
				$slug = mb_substr( $slug, 0, 80 );
			}

			$blocks     = '';
			$paragraphs = explode( "\n", $text );
			foreach ( $paragraphs as $p ) {
				$p = trim( $p );
				if ( '' !== $p ) {
					$blocks .= '<!-- wp:paragraph --><p>' . esc_html( $p ) . "</p><!-- /wp:paragraph -->\n";
				}
			}
			$blocks .= $image_html;

			$post_data = array(
				'post_title'   => $title,
				'post_content' => $blocks,
				'post_status'  => 'publish',
				'post_type'    => 'post',
				'post_date'    => $vk_date,
				'post_name'    => $slug,
				'meta_input'   => array( '_vk_post_id' => $vk_id ),
			);

			$post_id = wp_insert_post( $post_data, true );
			if ( is_wp_error( $post_id ) ) {
				$errors[] = 'Post ' . $vk_id . ': ' . $post_id->get_error_message();
				continue;
			}

			if ( $featured_id ) {
				set_post_thumbnail( $post_id, $featured_id );
			}

			++$imported;
		}

		return array(
			'done'     => true,
			'imported' => $imported,
			'skipped'  => $skipped,
			'errors'   => $errors,
			'total'    => isset( $body['response']['count'] ) ? (int) $body['response']['count'] : 0,
		);
	}

	private function upload_image( $url, $post_vk_id ) {
		if ( ! wp_http_validate_url( $url ) ) {
			return 0;
		}

		$tmp = download_url( $url );
		if ( is_wp_error( $tmp ) ) {
			return 0;
		}

		$raw_name = basename( wp_parse_url( $url, PHP_URL_PATH ) );
		$filename = sanitize_file_name( 'vk-' . $post_vk_id . '-' . $raw_name );
		if ( ! preg_match( '/\.(jpg|jpeg|png|gif|webp)$/i', $filename ) ) {
			$filename .= '.jpg';
		}

		$file = array(
			'name'     => $filename,
			'tmp_name' => $tmp,
		);

		$id = media_handle_sideload( $file, 0, null, array( 'post_status' => 'inherit' ) );

		if ( is_wp_error( $id ) ) {
			@unlink( $tmp );
			return 0;
		}

		return $id;
	}
}
