<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Scam_Dev_Nav_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<ul class="absolute top-full left-0 w-48 rounded-xl shadow-2xl py-2 z-50 hidden group-hover:block" style="background:var(--color-header-bg);backdrop-filter:blur(20px);border:2px solid var(--color-border);box-shadow:var(--shadow-md)">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul>';
	}

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$classes   = empty( $data_object->classes ) ? array() : (array) $data_object->classes;
		$has_kids  = in_array( 'menu-item-has-children', $classes );
		$is_active = in_array( 'current-menu-item', $classes ) || in_array( 'current-menu-parent', $classes );

		$li_class  = 'relative' . ( $has_kids ? ' group' : '' );
		$link_base = 'block px-4 py-2 rounded-lg text-sm font-medium transition-colors';

		if ( 0 === $depth ) {
			$link_class = $link_base . ' ' . ( $is_active ? 'text-white bg-coral-500/20' : 'text-gray-300 hover:text-white hover:bg-slate-500/10' );
		} else {
			$link_class = 'block px-4 py-2 mx-2 rounded-lg text-sm text-gray-400 hover:text-white hover:bg-slate-500/10 transition-colors';
		}

		$output .= '<li class="' . esc_attr( $li_class ) . '">';

		$atts           = array();
		$atts['href']   = ! empty( $data_object->url ) ? $data_object->url : '#';
		$atts['class']  = $link_class;
		$atts['target'] = ! empty( $data_object->target ) ? $data_object->target : '';
		$atts['rel']    = ! empty( $data_object->xfn ) ? $data_object->xfn : '';

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$title = apply_filters( 'the_title', $data_object->title, $data_object->ID );
		$title = $has_kids ? $title . ' <svg class="inline-block w-3 h-3 ml-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>' : $title;

		$output .= '<a' . $attributes . '>' . $title . '</a>';
	}

	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}
