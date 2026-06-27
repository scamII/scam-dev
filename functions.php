<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SCAM_DEV_VERSION', '1.0.0' );

add_filter( 'show_admin_bar', '__return_false' );

require get_template_directory() . '/inc/setup.php';
require get_template_directory() . '/inc/nav-walker.php';
require get_template_directory() . '/inc/enqueue.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/svg.php';
require get_template_directory() . '/inc/widget-matrix-stats.php';
require get_template_directory() . '/inc/widget-donate.php';
require get_template_directory() . '/inc/matrix-register.php';
require get_template_directory() . '/inc/highlightjs.php';
require get_template_directory() . '/inc/breadcrumbs.php';
require get_template_directory() . '/inc/schema.php';
require get_template_directory() . '/inc/seo.php';
require get_template_directory() . '/inc/admin-vk-import.php';
require get_template_directory() . '/inc/class-vk-import.php';
require get_template_directory() . '/inc/class-theme-updater.php';

function scam_dev_exclude_horses_from_blog( $query ) {
	if ( ! is_admin() && $query->is_home() && $query->is_main_query() ) {
		$cat = get_category_by_slug( 'loshadi' );
		if ( $cat ) {
			$exclude = $query->get( 'category__not_in' );
			$exclude = $exclude ? array_merge( (array) $exclude, array( $cat->term_id ) ) : array( $cat->term_id );
			$query->set( 'category__not_in', $exclude );
		}
	}
}
add_action( 'pre_get_posts', 'scam_dev_exclude_horses_from_blog' );

function reading_time() {
	$content = get_post_field( 'post_content', get_the_ID() );
	$words   = str_word_count( wp_strip_all_tags( $content ) );
	$minutes = ceil( $words / 200 );
	return sprintf( _n( '%d min read', '%d min read', $minutes, 'scam-dev' ), $minutes );
}
// Dynamic "My Account" menu item based on user role
add_filter(
	'wp_nav_menu_items',
	function ( $items, $args ) {
		if ( 'primary' !== $args->theme_location ) {
			return $items;
		}

		if ( ! is_user_logged_in() ) {
			$items .= '<li class="relative"><a href="' . esc_url( wp_login_url() ) . '" class="block px-4 py-2 rounded-lg text-sm font-medium transition-colors text-gray-300 hover:text-white hover:bg-slate-500/10">Войти</a></li>';
			return $items;
		}

		$user     = wp_get_current_user();
		$roles    = (array) $user->roles;
		$is_admin = array_intersect( $roles, array( 'administrator', 'editor' ) );
		$is_mod   = in_array( 'moderator', $roles ) || current_user_can( 'moderate_comments' );

		if ( $is_admin ) {
			$url   = admin_url();
			$label = 'Панель';
		} elseif ( $is_mod ) {
			$url   = admin_url( 'edit-comments.php' );
			$label = 'Модерация';
		} else {
			$url   = admin_url( 'profile.php' );
			$label = 'Профиль';
		}

		$items .= '<li class="relative group">';
		$items .= '<a href="' . esc_url( $url ) . '" class="block px-4 py-2 rounded-lg text-sm font-medium transition-colors text-gray-300 hover:text-white hover:bg-slate-500/10">';
		$items .= esc_html( $label );
		$items .= '<svg class="inline-block w-3 h-3 ml-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
		$items .= '</a>';
		$items .= '<ul class="absolute top-full right-0 w-48 rounded-xl shadow-2xl py-2 z-50 hidden group-hover:block" style="background:var(--color-header-bg);backdrop-filter:blur(20px);border:2px solid(var(--color-border, rgba(255,255,255,0.08)));box-shadow:var(--shadow-md)">';
		$items .= '<li><a href="' . esc_url( admin_url( 'profile.php' ) ) . '" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-slate-500/10 transition-colors">Профиль</a></li>';

		if ( $is_admin ) {
			$items .= '<li><a href="' . esc_url( admin_url() ) . '" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-slate-500/10 transition-colors">Консоль</a></li>';
			$items .= '<li><a href="' . esc_url( admin_url( 'customize.php' ) ) . '" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-slate-500/10 transition-colors">Настроить</a></li>';
		}

		$items .= '<li><a href="' . esc_url( wp_logout_url( home_url() ) ) . '" class="block px-4 py-2 text-sm text-coral-400 hover:text-white hover:bg-slate-500/10 transition-colors">Выйти</a></li>';
		$items .= '</ul>';
		$items .= '</li>';

		return $items;
	},
	10,
	2
);

function scam_dev_wp70_fix() {
	if ( is_admin() ) {
		wp_add_inline_script( 'wp-html-entities', 'window.wp=window.wp||{};window.wp.htmlEntities=window.wp.htmlEntities||{};if(!window.wp.htmlEntities.decodeEntities){window.wp.htmlEntities.decodeEntities=function(t){var e=document.createElement("textarea");e.innerHTML=t;return e.value}}' );
	}
}
add_action( 'admin_enqueue_scripts', 'scam_dev_wp70_fix' );
