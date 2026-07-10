<?php
/**
 * Primary menu walker.
 *
 * @package Scam_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tailwind-oriented menu walker.
 */
class Scam_Dev_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Start a submenu level.
	 *
	 * @param string   $output Output buffer.
	 * @param int      $depth  Menu depth.
	 * @param stdClass $args   Menu args.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		unset( $depth, $args );
		$output .= '<ul class="absolute top-full left-0 w-48 rounded-xl shadow-2xl py-2 z-50 hidden group-hover:block group-focus-within:block account-submenu">';
	}

	/**
	 * End a submenu level.
	 *
	 * @param string   $output Output buffer.
	 * @param int      $depth  Menu depth.
	 * @param stdClass $args   Menu args.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		unset( $depth, $args );
		$output .= '</ul>';
	}

	/**
	 * Start a menu item.
	 *
	 * @param string   $output            Output buffer.
	 * @param WP_Post  $data_object       Menu item.
	 * @param int      $depth             Menu depth.
	 * @param stdClass $args              Menu args.
	 * @param int      $current_object_id Current item ID.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		unset( $args, $current_object_id );

		$classes   = empty( $data_object->classes ) ? array() : (array) $data_object->classes;
		$has_kids  = in_array( 'menu-item-has-children', $classes, true );
		$is_active = in_array( 'current-menu-item', $classes, true )
			|| in_array( 'current-menu-parent', $classes, true );

		$li_class = 'relative' . ( $has_kids ? ' group' : '' );

		if ( 0 === $depth ) {
			$link_class = 'block px-4 py-2 rounded-lg text-sm font-medium transition-colors '
				. ( $is_active
					? 'text-white bg-coral-500/20'
					: 'text-gray-300 hover:text-white hover:bg-slate-500/10'
				);
		} else {
			$link_class = 'block px-4 py-2 mx-2 rounded-lg text-sm text-gray-400 hover:text-white hover:bg-slate-500/10 transition-colors';
		}

		$output .= '<li class="' . esc_attr( $li_class ) . '">';

		$attributes = array(
			'href'   => ! empty( $data_object->url ) ? $data_object->url : '#',
			'class'  => $link_class,
			'target' => ! empty( $data_object->target ) ? $data_object->target : '',
			'rel'    => ! empty( $data_object->xfn ) ? $data_object->xfn : '',
		);

		if ( $has_kids ) {
			$attributes['aria-haspopup'] = 'true';
		}

		$attribute_html = '';
		foreach ( $attributes as $attribute => $value ) {
			if ( '' === $value ) {
				continue;
			}

			$value = 'href' === $attribute ? esc_url( $value ) : esc_attr( $value );
			$attribute_html .= ' ' . esc_attr( $attribute ) . '="' . $value . '"';
		}

		$title = apply_filters(
			'the_title',
			$data_object->title,
			$data_object->ID
		);

		$output .= '<a' . $attribute_html . '>';
		$output .= esc_html( $title );

		if ( $has_kids ) {
			$output .= ' <span aria-hidden="true">▾</span>';
		}

		$output .= '</a>';
	}

	/**
	 * End a menu item.
	 *
	 * @param string   $output      Output buffer.
	 * @param WP_Post  $data_object Menu item.
	 * @param int      $depth       Menu depth.
	 * @param stdClass $args        Menu args.
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		unset( $data_object, $depth, $args );
		$output .= '</li>';
	}
}
