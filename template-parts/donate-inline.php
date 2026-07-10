<?php
/**
 * Optional inline donation block supplied by the donation plugin.
 *
 * @package Scam_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( shortcode_exists( 'scamdev_donate_inline' ) ) {
	echo do_shortcode( '[scamdev_donate_inline]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
