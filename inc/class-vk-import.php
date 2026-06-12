<?php

class Scam_Dev_VK_Import {

    private $token;
    private $owner_id;

    public function __construct( $token, $owner_id ) {
        $this->token    = $token;
        $this->owner_id = $owner_id;
    }

    public function run( $count = 20, $offset = 0 ) {
        $url = add_query_arg( array(
            'owner_id'      => $this->owner_id,
            'count'         => $count,
            'offset'        => $offset,
            'filter'        => 'owner',
            'extended'      => 1,
            'v'             => '5.199',
            'access_token'  => $this->token,
        ), 'https://api.vk.com/method/wall.get' );

        $response = wp_remote_get( $url, array( 'timeout' => 30 ) );
        if ( is_wp_error( $response ) ) {
            return array( 'done' => false, 'error' => 'HTTP error: ' . $response->get_error_message() );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( isset( $body['error'] ) ) {
            return array( 'done' => false, 'error' => $body['error']['error_msg'] );
        }

        $posts = $body['response'];
        if ( ! isset( $posts['items'] ) || empty( $posts['items'] ) ) {
            return array( 'done' => false, 'error' => 'Постов не найдено' );
        }

        $imported = 0;
        $skipped  = 0;
        $errors   = array();

        foreach ( $posts['items'] as $post ) {
            $vk_id   = $post['id'];
            $vk_date = gmdate( 'Y-m-d H:i:s', $post['date'] );
            $text    = isset( $post['text'] ) ? trim( $post['text'] ) : '';

            // Get text from link attachment if post has no text
            if ( empty( $text ) && isset( $post['attachments'][0]['link']['description'] ) ) {
                $text = $post['attachments'][0]['link']['description'];
            }
            if ( empty( $text ) && isset( $post['attachments'][0]['link']['title'] ) ) {
                $text = $post['attachments'][0]['link']['title'];
            }

            $text = preg_replace( '/\[(?:https?:\/\/[^\|\]]+)\|([^\]]+)\]/u', '$1', $text );
            $text = preg_replace( '/\[(?:id|club)\d+\|([^\]]+)\]/u', '$1', $text );

            if ( empty( $text ) ) {
                $skipped++;
                continue;
            }

            // Skip if already imported
            $existing = get_posts( array(
                'post_type'      => 'post',
                'post_status'    => 'any',
                'meta_key'       => '_vk_post_id',
                'meta_value'     => $vk_id,
                'posts_per_page' => 1,
            ) );

            if ( ! empty( $existing ) ) {
                $skipped++;
                continue;
            }

            // Download photos
            $image_html  = '';
            $featured_id = 0;
            if ( ! empty( $post['attachments'] ) ) {
                foreach ( $post['attachments'] as $att ) {
                    if ( $att['type'] === 'photo' && isset( $att['photo']['sizes'] ) ) {
                        $sizes   = $att['photo']['sizes'];
                        $largest = end( $sizes );
                        $img_url = $largest['url'];

                        $thumb_id = $this->upload_image( $img_url, $vk_id );
                        if ( $thumb_id && ! $featured_id ) {
                            $featured_id = $thumb_id;
                        }
                        if ( $thumb_id ) {
                            $img_src = wp_get_attachment_url( $thumb_id );
                            $image_html .= "\n<!-- wp:image -->\n<figure class=\"wp-block-image\"><img src=\"" . esc_url( $img_src ) . "\" alt=\"\"/></figure>\n<!-- /wp:image -->\n";
                        }
                    }
                    if ( $att['type'] === 'video' && isset( $att['video'] ) ) {
                        $v        = $att['video'];
                        $video_id = $v['owner_id'] . '_' . $v['id'];
                        $v_url    = 'https://vk.com/video' . $video_id;
                        $title    = isset( $v['title'] ) ? $v['title'] : 'Видео';
                        $duration = isset( $v['duration'] ) ? sprintf( '%d:%02d', intval( $v['duration'] / 60 ), $v['duration'] % 60 ) : '';

                        $image_html .= "\n<!-- wp:paragraph -->\n<p>";
                        $image_html .= '🎬 <strong>' . esc_html( $title ) . '</strong>';
                        if ( $duration ) {
                            $image_html .= ' (' . esc_html( $duration ) . ')';
                        }
                        $image_html .= ' — <a href="' . esc_url( $v_url ) . '" target="_blank" rel="noopener">Смотреть на VK</a>';
                        $image_html .= "</p>\n<!-- /wp:paragraph -->\n";
                    }
                }
            }

            // Build post
            $lines   = explode( "\n", $text );
            $title   = wp_trim_words( $lines[0], 12, '' );
            $slug    = sanitize_title( $title );
            if ( mb_strlen( $slug ) > 80 ) {
                $slug = mb_substr( $slug, 0, 80 );
            }

            $blocks  = '';
            $paragraphs = explode( "\n", $text );
            foreach ( $paragraphs as $p ) {
                $p = trim( $p );
                if ( ! empty( $p ) ) {
                    $blocks .= "<!-- wp:paragraph --><p>" . esc_html( $p ) . "</p><!-- /wp:paragraph -->\n";
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
                $errors[] = "Пост {$vk_id}: " . $post_id->get_error_message();
                continue;
            }

            if ( $featured_id ) {
                set_post_thumbnail( $post_id, $featured_id );
            }

            $imported++;
        }

        return array(
            'done'     => true,
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
            'total'    => $posts['count'],
        );
    }

    private function upload_image( $url, $post_vk_id ) {
        $tmp = download_url( $url );
        if ( is_wp_error( $tmp ) ) {
            return 0;
        }

        $filename = 'vk-' . $post_vk_id . '-' . basename( wp_parse_url( $url, PHP_URL_PATH ) );
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
