<?php

function scam_dev_meta_tags() {
	echo '<meta name="description" content="' . esc_attr( scam_dev_get_description() ) . '">' . "\n";

	$title = wp_get_document_title();
	$url   = scam_dev_get_canonical_url();
	$desc  = scam_dev_get_description();
	$img   = scam_dev_get_og_image();
	$type  = is_single() ? 'article' : 'website';

	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '">' . "\n";

	if ( $img ) {
		echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
		$img_size = scam_dev_get_image_size( $img );
		if ( $img_size ) {
			echo '<meta property="og:image:width" content="' . intval( $img_size['width'] ) . '">' . "\n";
			echo '<meta property="og:image:height" content="' . intval( $img_size['height'] ) . '">' . "\n";
		}
	}

	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
	if ( $img ) {
		echo '<meta name="twitter:image" content="' . esc_url( $img ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'scam_dev_meta_tags', 5 );

function scam_dev_get_description() {
	if ( is_single() || is_page() ) {
		$excerpt = get_the_excerpt();
		if ( $excerpt ) {
			return wp_strip_all_tags( $excerpt );
		}
	}
	if ( is_category() || is_tag() ) {
		$desc = term_description();
		if ( $desc ) {
			return wp_strip_all_tags( $desc );
		}
	}
	return get_bloginfo( 'description', 'display' );
}

function scam_dev_get_canonical_url() {
	if ( is_single() || is_page() ) {
		return get_permalink();
	}
	if ( is_category() || is_tag() ) {
		return get_term_link( get_queried_object_id() );
	}
	return home_url( '/' );
}

function scam_dev_get_og_image() {
	if ( is_single() && has_post_thumbnail() ) {
		return get_the_post_thumbnail_url( null, 'large' );
	}
	return '';
}

function scam_dev_get_image_size( $url ) {
	$path = str_replace( wp_get_upload_dir()['baseurl'], wp_get_upload_dir()['basedir'], $url );
	if ( file_exists( $path ) ) {
		$size = getimagesize( $path );
		if ( $size ) {
			return array( 'width' => $size[0], 'height' => $size[1] );
		}
	}
	return false;
}
