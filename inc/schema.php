<?php

function scam_dev_schema_jsonld() {
	if ( ! is_single() ) {
		return;
	}

	$post         = get_post();
	$author_name  = get_the_author_meta( 'display_name', $post->post_author );
	$author_url   = get_author_posts_url( $post->post_author );
	$cats         = get_the_category();
	$cat_name     = $cats ? $cats[0]->name : '';
	$featured_img = has_post_thumbnail() ? get_the_post_thumbnail_url( $post, 'large' ) : '';

	$schema = array(
		'@context'      => 'https://schema.org',
		'@type'         => 'BlogPosting',
		'mainEntityOfPage' => get_permalink(),
		'headline'      => get_the_title(),
		'datePublished' => get_the_date( 'c' ),
		'dateModified'  => get_the_modified_date( 'c' ),
		'author'        => array(
			'@type' => 'Person',
			'name'  => $author_name,
			'url'   => $author_url,
		),
		'publisher'     => array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url(),
		),
		'description'   => wp_strip_all_tags( get_the_excerpt() ),
		'url'           => get_permalink(),
	);

	if ( $cat_name ) {
		$schema['articleSection'] = $cat_name;
	}

	if ( $featured_img ) {
		$schema['image'] = $featured_img;
	}

	if ( has_tag() ) {
		$tags        = get_the_tags();
		$tag_names   = wp_list_pluck( $tags, 'name' );
		$schema['keywords'] = implode( ', ', $tag_names );
	}

	echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
add_action( 'wp_head', 'scam_dev_schema_jsonld' );
