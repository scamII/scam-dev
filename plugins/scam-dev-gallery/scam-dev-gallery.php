<?php
/**
 * Plugin Name: Scam Dev Gallery
 * Description: Minimal gallery with grid layout and lightbox. Shortcode: [scamdev_gallery]
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SCAMDEV_GALLERY_VERSION', '1.0.0' );

// --- CPT ---
add_action(
	'init',
	function () {
		register_post_type(
			'scamdev_gallery',
			array(
				'labels'        => array(
					'name'          => 'Галереи',
					'singular_name' => 'Галерея',
					'add_new'       => 'Добавить галерею',
					'add_new_item'  => 'Новая галерея',
					'edit_item'     => 'Редактировать галерею',
				),
				'public'        => true,
				'has_archive'   => false,
				'show_in_rest'  => true,
				'menu_icon'     => 'dashicons-format-gallery',
				'supports'      => array( 'title', 'thumbnail', 'editor' ),
				'menu_position' => 21,
			)
		);
	}
);

// --- Metabox with image picker ---
add_action(
	'add_meta_boxes',
	function () {
		add_meta_box(
			'scamdev_gallery_images',
			'Изображения',
			function ( $post ) {
				$images = get_post_meta( $post->ID, '_scamdev_gallery_ids', true ) ?: array();
				if ( is_string( $images ) ) {
					$images = explode( ',', $images );
				}

				wp_nonce_field( 'scamdev_gallery_save', 'scamdev_gallery_nonce' );
				?>
		<div id="scamdev-gallery-wrapper">
			<input type="hidden" name="scamdev_gallery_ids" id="scamdev-gallery-ids"
						value="<?php echo esc_attr( implode( ',', $images ) ); ?>">
			<div id="scamdev-gallery-preview" style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
					<?php
					foreach ( $images as $id ) :
						$img = wp_get_attachment_image_src( $id, 'thumbnail' );
						if ( $img ) :
							?>
						<div style="position:relative;width:100px;height:100px;">
								<img src="<?php echo esc_url( $img[0] ); ?>" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">
							<button type="button" class="scamdev-remove-img"
									style="position:absolute;top:-6px;right:-6px;background:#dc2626;color:#fff;border:none;
											border-radius:50%;width:20px;height:20px;cursor:pointer;font-size:12px;line-height:1;">
								✕
							</button>
						</div>
							<?php
						endif;
					endforeach;
					?>
			</div>
			<button type="button" class="button" id="scamdev-add-images">
					<?php esc_html_e( 'Выбрать изображения', 'scam-dev-gallery' ); ?>
			</button>
		</div>
				<?php
			},
			'scamdev_gallery',
			'normal',
			'high'
		);
	}
);

add_action(
	'save_post',
	function ( $post_id ) {
		if ( ! isset( $_POST['scamdev_gallery_nonce'] )
		|| ! wp_verify_nonce( $_POST['scamdev_gallery_nonce'], 'scamdev_gallery_save' )
		|| defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE
		|| ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( isset( $_POST['scamdev_gallery_ids'] ) ) {
			$raw_ids = wp_unslash( $_POST['scamdev_gallery_ids'] );
			$ids     = array_filter( array_map( 'absint', explode( ',', $raw_ids ) ) );
			update_post_meta( $post_id, '_scamdev_gallery_ids', implode( ',', $ids ) );
		}
	}
);

// --- Admin JS/CSS ---
add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		global $post;
		if ( ! $post || $post->post_type !== 'scamdev_gallery' ) {
			return;
		}

		wp_enqueue_media();
		wp_add_inline_script(
			'jquery',
			'
        jQuery(function($) {
            var frame;
            $("#scamdev-add-images").on("click", function(e) {
                e.preventDefault();
                if (frame) { frame.open(); return; }
                frame = wp.media({
                    title: "Выберите изображения",
                    button: { text: "Добавить" },
                    multiple: true,
                });
                frame.on("select", function() {
                    var ids = $("#scamdev-gallery-ids").val().split(",").filter(Boolean);
                    frame.state().get("selection").each(function(a) {
                        if (ids.indexOf(a.id.toString()) === -1) ids.push(a.id);
                    });
                    $("#scamdev-gallery-ids").val(ids.join(","));
                    refreshPreview(ids);
                });
                frame.open();
            });

            $("#scamdev-gallery-preview").on("click", ".scamdev-remove-img", function() {
                var idx = $(this).parent().index();
                var ids = $("#scamdev-gallery-ids").val().split(",").filter(Boolean);
                ids.splice(idx, 1);
                $("#scamdev-gallery-ids").val(ids.join(","));
                $(this).parent().remove();
            });

            function refreshPreview(ids) {
                var p = $("#scamdev-gallery-preview");
                p.empty();
                ids.forEach(function(id) {
                    $.post(ajaxurl, {action:"scamdev_preview_img", id:id, nonce:"' . wp_create_nonce( 'scamdev_preview' ) . '"}, function(r) {
                        if (r) p.append(r);
                    });
                });
            }
        });
    '
		);

		add_action(
			'wp_ajax_scamdev_preview_img',
			function () {
				check_ajax_referer( 'scamdev_preview', 'nonce' );
				if ( ! current_user_can( 'upload_files' ) ) {
					wp_die( -1, 403 );
				}
				$id  = intval( $_POST['id'] );
				$img = wp_get_attachment_image_src( $id, 'thumbnail' );
				if ( ! $img ) {
					wp_die();
				}
				echo '<div style="position:relative;width:100px;height:100px;">
                <img src="' . esc_url( $img[0] ) . '" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">
            <button type="button" class="scamdev-remove-img"
                    style="position:absolute;top:-6px;right:-6px;background:#dc2626;color:#fff;border:none;
                           border-radius:50%;width:20px;height:20px;cursor:pointer;font-size:12px;line-height:1;">✕</button>
            </div>';
				wp_die();
			}
		);
	}
);

// --- Shortcode ---
add_shortcode(
	'scamdev_gallery',
	function ( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'   => 0,
				'cols' => 3,
			),
			$atts
		);
		$ids  = $atts['id'] ? array( intval( $atts['id'] ) ) : array();
		if ( empty( $ids ) ) {
			$q   = get_posts(
				array(
					'post_type'      => 'scamdev_gallery',
					'posts_per_page' => -1,
					'orderby'        => 'date',
					'order'          => 'DESC',
					'fields'         => 'ids',
				)
			);
			$ids = $q;
		}
		if ( empty( $ids ) ) {
			return '';
		}

		$cols = max( 2, min( 6, intval( $atts['cols'] ) ) );
		$all  = array();
		foreach ( $ids as $gid ) {
			$img_ids = get_post_meta( $gid, '_scamdev_gallery_ids', true );
			if ( ! $img_ids ) {
				continue;
			}
			$img_ids = is_string( $img_ids ) ? explode( ',', $img_ids ) : $img_ids;
			foreach ( $img_ids as $img_id ) {
				$full  = wp_get_attachment_image_src( $img_id, 'full' );
				$thumb = wp_get_attachment_image_src( $img_id, 'medium_large' );
				$alt   = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
				if ( $full && $thumb ) {
					$all[] = array(
						'full'  => $full[0],
						'thumb' => $thumb[0],
						'alt'   => $alt,
					);
				}
			}
		}
		if ( empty( $all ) ) {
			return '';
		}

		ob_start();
		?>
	<div class="scamdev-gallery" style="--cols:<?php echo $cols; ?>">
		<?php foreach ( $all as $i => $img ) : ?>
			<a href="<?php echo esc_url( $img['full'] ); ?>" class="scamdev-gallery-item"
				data-index="<?php echo $i; ?>" aria-label="<?php echo esc_attr( $img['alt'] ); ?>">
				<img src="<?php echo esc_url( $img['thumb'] ); ?>"
					alt="<?php echo esc_attr( $img['alt'] ); ?>" loading="lazy">
			</a>
		<?php endforeach; ?>
	</div>

	<div class="scamdev-lightbox" id="scamdev-lightbox" aria-hidden="true">
		<button class="scamdev-lightbox-close" aria-label="Закрыть">&times;</button>
		<button class="scamdev-lightbox-prev" aria-label="Назад">&#8249;</button>
		<button class="scamdev-lightbox-next" aria-label="Вперёд">&#8250;</button>
		<img src="" alt="" class="scamdev-lightbox-img">
	</div>
		<?php
		return ob_get_clean();
	}
);

// --- Frontend CSS/JS ---
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! has_shortcode( get_the_content() ?? '', 'scamdev_gallery' )
		&& ! has_block( 'scamdev/gallery', get_the_content() ?? '' ) ) {
			return;
		}

		wp_add_inline_style(
			'scam-dev-style',
			'
        .scamdev-gallery {
            display: grid;
            grid-template-columns: repeat(var(--cols, 3), 1fr);
            gap: 8px;
            margin: 2rem 0;
        }
        @media (max-width: 768px) {
            .scamdev-gallery { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .scamdev-gallery { grid-template-columns: 1fr; }
        }
        .scamdev-gallery-item {
            display: block;
            overflow: hidden;
            border-radius: 8px;
            cursor: pointer;
            aspect-ratio: 4/3;
        }
        .scamdev-gallery-item:hover { opacity: 0.85; }
        .scamdev-gallery-item:nth-child(6n+1),
        .scamdev-gallery-item:nth-child(6n+5) {
            grid-row: span 2;
            aspect-ratio: auto;
        }
        .scamdev-gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        .scamdev-gallery-item:hover img { transform: scale(1.03); }

        .scamdev-lightbox {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.92);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .scamdev-lightbox.active { display: flex; }
        .scamdev-lightbox-img {
            max-width: 90vw;
            max-height: 85vh;
            object-fit: contain;
            border-radius: 4px;
        }
        .scamdev-lightbox-close {
            position: absolute;
            top: 16px;
            right: 24px;
            background: none;
            border: none;
            color: #fff;
            font-size: 36px;
            cursor: pointer;
            z-index: 2;
            padding: 8px;
            line-height: 1;
        }
        .scamdev-lightbox-prev,
        .scamdev-lightbox-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.12);
            border: none;
            color: #fff;
            font-size: 48px;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 4px;
            z-index: 2;
            line-height: 1;
        }
        .scamdev-lightbox-prev:hover,
        .scamdev-lightbox-next:hover { background: rgba(255,255,255,0.25); }
        .scamdev-lightbox-prev { left: 16px; }
        .scamdev-lightbox-next { right: 16px; }
    '
		);

		wp_add_inline_script(
			'scam-dev-main',
			'
    (function(){
        var items = document.querySelectorAll(".scamdev-gallery-item");
        var lb = document.getElementById("scamdev-lightbox");
        var img = lb ? lb.querySelector(".scamdev-lightbox-img") : null;
        var idx = 0;
        if (!lb || !img) return;

        items.forEach(function(el, i) {
            el.addEventListener("click", function(e) {
                e.preventDefault();
                idx = parseInt(el.dataset.index);
                show(idx);
                lb.classList.add("active");
                lb.setAttribute("aria-hidden", "false");
                document.body.style.overflow = "hidden";
            });
        });

        function show(i) {
            idx = i;
            var item = items[i];
            if (item) img.src = item.href;
        }

        lb.querySelector(".scamdev-lightbox-close").addEventListener("click", close);
        lb.querySelector(".scamdev-lightbox-prev").addEventListener("click", function() {
            show(idx > 0 ? idx - 1 : items.length - 1);
        });
        lb.querySelector(".scamdev-lightbox-next").addEventListener("click", function() {
            show(idx < items.length - 1 ? idx + 1 : 0);
        });
        lb.addEventListener("click", function(e) {
            if (e.target === lb) close();
        });
        document.addEventListener("keydown", function(e) {
            if (!lb.classList.contains("active")) return;
            if (e.key === "Escape") close();
            if (e.key === "ArrowLeft") lb.querySelector(".scamdev-lightbox-prev").click();
            if (e.key === "ArrowRight") lb.querySelector(".scamdev-lightbox-next").click();
        });

        function close() {
            lb.classList.remove("active");
            lb.setAttribute("aria-hidden", "true");
            document.body.style.overflow = "";
        }
    })();
    '
		);
	}
);

// --- Gutenberg block ---
add_action(
	'init',
	function () {
		register_block_type( __DIR__ . '/block.json' );
	}
);
