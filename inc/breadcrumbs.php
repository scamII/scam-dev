<?php

function scam_dev_breadcrumbs() {
	$sep = ' <span class="mx-2 text-slate-500/40">/</span> ';
	$home = '<a href="' . esc_url( home_url( '/' ) ) . '" class="text-gray-500 hover:text-coral-400 transition-colors">' . esc_html__( 'Главная', 'scam-dev' ) . '</a>';

	if ( is_front_page() ) {
		return;
	}

	echo '<nav class="flex flex-wrap items-center text-sm py-4 border-b border-slate-500/10 mb-8" aria-label="' . esc_attr__( 'Хлебные крошки', 'scam-dev' ) . '">';
	echo $home;

	if ( is_single() ) {
		$cats = get_the_category();
		if ( $cats ) {
			echo $sep;
			echo '<a href="' . esc_url( get_category_link( $cats[0]->term_id ) ) . '" class="text-gray-500 hover:text-coral-400 transition-colors">' . esc_html( $cats[0]->name ) . '</a>';
		}
		echo $sep;
		echo '<span class="text-gray-300">' . get_the_title() . '</span>';
	} elseif ( is_page() ) {
		echo $sep;
		echo '<span class="text-gray-300">' . get_the_title() . '</span>';
	} elseif ( is_category() || is_tag() || is_tax() ) {
		echo $sep;
		echo '<span class="text-gray-300">' . single_term_title( '', false ) . '</span>';
	} elseif ( is_search() ) {
		echo $sep;
		echo '<span class="text-gray-300">' . sprintf( esc_html__( 'Поиск: %s', 'scam-dev' ), get_search_query() ) . '</span>';
	} elseif ( is_archive() ) {
		if ( is_year() ) {
			echo $sep . '<span class="text-gray-300">' . get_the_date( 'Y' ) . '</span>';
		} elseif ( is_month() ) {
			echo $sep . '<span class="text-gray-300">' . get_the_date( 'F Y' ) . '</span>';
		} else {
			echo $sep . '<span class="text-gray-300">' . esc_html__( 'Архивы', 'scam-dev' ) . '</span>';
		}
	} elseif ( is_404() ) {
		echo $sep . '<span class="text-gray-300">404</span>';
	}

	echo '</nav>';
}
