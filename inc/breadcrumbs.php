<?php
/**
 * Breadcrumb navigation.
 *
 * @package Scam_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render escaped breadcrumbs for common WordPress views.
 */
function scam_dev_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	$separator = ' <span class="mx-2 text-slate-500/40" aria-hidden="true">/</span> ';
	$home_url  = esc_url( home_url( '/' ) );
	$home_text = esc_html__( 'Главная', 'scam-dev' );

	echo '<nav class="flex flex-wrap items-center text-sm py-4 '
		. 'border-b border-slate-500/10 mb-8" aria-label="'
		. esc_attr__( 'Хлебные крошки', 'scam-dev' ) . '">';
	echo '<a href="' . $home_url . '" class="text-gray-500 '
		. 'hover:text-coral-400 transition-colors">' . $home_text . '</a>';

	if ( is_single() ) {
		$categories = get_the_category();

		if ( $categories ) {
			$category_url = get_category_link( $categories[0]->term_id );

			if ( ! is_wp_error( $category_url ) ) {
				echo $separator; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<a href="' . esc_url( $category_url ) . '" '
					. 'class="text-gray-500 hover:text-coral-400 '
					. 'transition-colors">'
					. esc_html( $categories[0]->name ) . '</a>';
			}
		}

		echo $separator; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<span class="text-gray-300">'
			. esc_html( get_the_title() ) . '</span>';
	} elseif ( is_page() ) {
		echo $separator; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<span class="text-gray-300">'
			. esc_html( get_the_title() ) . '</span>';
	} elseif ( is_category() || is_tag() || is_tax() ) {
		echo $separator; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<span class="text-gray-300">'
			. esc_html( single_term_title( '', false ) ) . '</span>';
	} elseif ( is_search() ) {
		echo $separator; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<span class="text-gray-300">'
			. esc_html(
				sprintf(
					/* translators: %s: search query. */
					__( 'Поиск: %s', 'scam-dev' ),
					get_search_query()
				)
			) . '</span>';
	} elseif ( is_archive() ) {
		echo $separator; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<span class="text-gray-300">'
			. esc_html( get_the_archive_title() ) . '</span>';
	} elseif ( is_404() ) {
		echo $separator; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<span class="text-gray-300">404</span>';
	}

	echo '</nav>';
}
