<?php
/**
 * Dynamic gallery block renderer.
 *
 * @package Scam_Dev_Gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$columns    = isset( $attributes['cols'] ) ? absint( $attributes['cols'] ) : 3;
$gallery_id = isset( $attributes['galleryId'] ) ? absint( $attributes['galleryId'] ) : 0;
$gallery_ids = $gallery_id ? array( $gallery_id ) : array();

echo scamdev_gallery_render( $gallery_ids, $columns ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
