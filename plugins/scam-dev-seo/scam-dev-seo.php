<?php
/**
 * Plugin Name: Scam Dev SEO
 * Description: Open Graph, Twitter Card и безопасная Schema.org JSON-LD разметка.
 * Version: 1.3.0
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Author: Scam Dev
 * License: GPL v2 or later
 * Text Domain: scam-dev-seo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Small SEO metadata provider.
 */
final class Scam_Dev_SEO {

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'render_meta' ), 5 );
		add_action( 'wp_head', array( __CLASS__, 'render_schema' ), 20 );
	}

	/**
	 * Whether another full SEO plugin is active.
	 *
	 * @return bool
	 */
	private static function has_conflicting_plugin() {
		$conflict = defined( 'WPSEO_VERSION' )
			|| defined( 'RANK_MATH_VERSION' )
			|| defined( 'AIOSEO_VERSION' )
			|| class_exists( '\AIOSEO\Plugin\Common\Main' )
			|| class_exists( '\The_SEO_Framework\Load' );

		/**
		 * Filter whether Scam Dev SEO should yield to another plugin.
		 *
		 * @param bool $conflict Whether a conflict was detected.
		 */
		return (bool) apply_filters( 'scam_dev_seo_has_conflict', $conflict );
	}

	/**
	 * Render meta description and social metadata.
	 */
	public static function render_meta() {
		if ( is_admin() || self::has_conflicting_plugin() ) {
			return;
		}

		$title       = wp_get_document_title();
		$description = self::get_description();
		$url         = self::get_current_url();
		$image       = self::get_image();
		$type        = is_singular( 'post' ) ? 'article' : 'website';

		if ( $description ) {
			echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
		}

		if ( ! is_search() && ! is_404() ) {
			echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
		}

		echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
		echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
		echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
		echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
		echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '">' . "\n";
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
		echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";

		if ( $image ) {
			echo '<meta property="og:image" content="' . esc_url( $image['url'] ) . '">' . "\n";
			echo '<meta name="twitter:image" content="' . esc_url( $image['url'] ) . '">' . "\n";

			if ( $image['width'] && $image['height'] ) {
				echo '<meta property="og:image:width" content="' . esc_attr( $image['width'] ) . '">' . "\n";
				echo '<meta property="og:image:height" content="' . esc_attr( $image['height'] ) . '">' . "\n";
			}

			if ( $image['alt'] ) {
				echo '<meta property="og:image:alt" content="' . esc_attr( $image['alt'] ) . '">' . "\n";
				echo '<meta name="twitter:image:alt" content="' . esc_attr( $image['alt'] ) . '">' . "\n";
			}
		}
	}

	/**
	 * Render BlogPosting JSON-LD.
	 */
	public static function render_schema() {
		if ( ! is_singular( 'post' ) || self::has_conflicting_plugin() ) {
			return;
		}

		$post = get_post();

		if ( ! $post ) {
			return;
		}

		$image      = self::get_image();
		$categories = get_the_category( $post->ID );
		$tags       = get_the_tags( $post->ID );

		$schema = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'BlogPosting',
			'mainEntityOfPage' => array(
				'@type' => 'WebPage',
				'@id'   => get_permalink( $post ),
			),
			'headline'         => get_the_title( $post ),
			'datePublished'    => get_the_date( DATE_W3C, $post ),
			'dateModified'     => get_the_modified_date( DATE_W3C, $post ),
			'author'           => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $post->post_author ),
				'url'   => get_author_posts_url( $post->post_author ),
			),
			'publisher'        => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url( '/' ),
			),
			'description'      => self::get_description(),
			'url'              => get_permalink( $post ),
		);

		if ( $image ) {
			$schema['image'] = array(
				'@type'  => 'ImageObject',
				'url'    => $image['url'],
				'width'  => $image['width'],
				'height' => $image['height'],
			);
		}

		if ( $categories ) {
			$schema['articleSection'] = $categories[0]->name;
		}

		if ( $tags ) {
			$schema['keywords'] = implode(
				', ',
				wp_list_pluck( $tags, 'name' )
			);
		}

		$encoded = wp_json_encode(
			$schema,
			JSON_UNESCAPED_UNICODE
			| JSON_HEX_TAG
			| JSON_HEX_AMP
			| JSON_HEX_APOS
			| JSON_HEX_QUOT
		);

		if ( false !== $encoded ) {
			echo '<script type="application/ld+json">' . $encoded . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Build a plain-text description.
	 *
	 * @return string
	 */
	private static function get_description() {
		if ( is_singular() ) {
			$excerpt = get_the_excerpt();

			if ( $excerpt ) {
				return wp_trim_words(
					wp_strip_all_tags( $excerpt ),
					35,
					'…'
				);
			}
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$description = term_description();

			if ( $description ) {
				return wp_trim_words(
					wp_strip_all_tags( $description ),
					35,
					'…'
				);
			}
		}

		return wp_strip_all_tags(
			get_bloginfo( 'description', 'display' )
		);
	}

	/**
	 * Return the actual queried URL.
	 *
	 * @return string
	 */
	private static function get_current_url() {
		if ( is_singular() ) {
			return (string) get_permalink();
		}

		if ( is_search() || is_archive() || is_home() ) {
			return get_pagenum_link(
				max( 1, (int) get_query_var( 'paged' ) )
			);
		}

		return home_url( '/' );
	}

	/**
	 * Return social image data from WordPress attachment metadata.
	 *
	 * @return array{url:string,width:int,height:int,alt:string}|false
	 */
	private static function get_image() {
		if ( ! is_singular() || ! has_post_thumbnail() ) {
			return false;
		}

		$attachment_id = get_post_thumbnail_id();
		$image         = wp_get_attachment_image_src(
			$attachment_id,
			'large'
		);

		if ( ! $image ) {
			return false;
		}

		return array(
			'url'    => $image[0],
			'width'  => (int) $image[1],
			'height' => (int) $image[2],
			'alt'    => (string) get_post_meta(
				$attachment_id,
				'_wp_attachment_image_alt',
				true
			),
		);
	}
}

Scam_Dev_SEO::init();
