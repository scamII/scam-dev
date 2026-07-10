<?php
/**
 * Scam Dev theme bootstrap.
 *
 * @package Scam_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$scam_dev_theme = wp_get_theme( get_template() );
define( 'SCAM_DEV_VERSION', $scam_dev_theme->get( 'Version' ) ?: '1.3.0' );
unset( $scam_dev_theme );

require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/nav-walker.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/hero.php';
require_once get_template_directory() . '/inc/highlightjs.php';
require_once get_template_directory() . '/inc/breadcrumbs.php';
require_once get_template_directory() . '/inc/class-theme-updater.php';

/**
 * Exclude the horses category from the regular blog index.
 *
 * @param WP_Query $query Main query.
 */
function scam_dev_exclude_horses_from_blog( $query ) {
	if ( is_admin() || ! $query->is_home() || ! $query->is_main_query() ) {
		return;
	}

	$category = get_category_by_slug( 'loshadi' );

	if ( ! $category ) {
		return;
	}

	$excluded   = (array) $query->get( 'category__not_in' );
	$excluded[] = (int) $category->term_id;

	$query->set( 'category__not_in', array_values( array_unique( $excluded ) ) );
}
add_action( 'pre_get_posts', 'scam_dev_exclude_horses_from_blog' );

/**
 * Calculate an approximate Unicode-aware reading time.
 *
 * @param int|WP_Post|null $post Post object or ID.
 * @return string
 */
function scam_dev_reading_time( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return '';
	}

	$content = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
	preg_match_all( '/[\p{L}\p{N}]+/u', $content, $matches );

	$word_count = isset( $matches[0] ) ? count( $matches[0] ) : 0;
	$minutes    = max( 1, (int) ceil( $word_count / 200 ) );

	return sprintf(
		/* translators: %d: number of minutes. */
		_n( '%d минута чтения', '%d минут чтения', $minutes, 'scam-dev' ),
		$minutes
	);
}

/**
 * Add an account link to the primary menu.
 *
 * @param string   $items Existing menu markup.
 * @param stdClass $args  Menu arguments.
 * @return string
 */
function scam_dev_add_account_menu_item( $items, $args ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $items;
	}

	if ( ! is_user_logged_in() ) {
		$items .= sprintf(
			'<li class="relative"><a href="%1$s" '
			. 'class="block px-4 py-2 rounded-lg text-sm font-medium '
			. 'transition-colors text-gray-300 hover:text-white '
			. 'hover:bg-slate-500/10">%2$s</a></li>',
			esc_url( wp_login_url() ),
			esc_html__( 'Войти', 'scam-dev' )
		);

		return $items;
	}

	if ( current_user_can( 'manage_options' ) ) {
		$primary_url   = admin_url();
		$primary_label = __( 'Панель', 'scam-dev' );
	} elseif ( current_user_can( 'moderate_comments' ) ) {
		$primary_url   = admin_url( 'edit-comments.php' );
		$primary_label = __( 'Модерация', 'scam-dev' );
	} else {
		$primary_url   = admin_url( 'profile.php' );
		$primary_label = __( 'Профиль', 'scam-dev' );
	}

	$items .= '<li class="relative group">';
	$items .= sprintf(
		'<a href="%1$s" class="block px-4 py-2 rounded-lg '
		. 'text-sm font-medium transition-colors text-gray-300 '
		. 'hover:text-white hover:bg-slate-500/10">%2$s '
		. '<span aria-hidden="true">▾</span></a>',
		esc_url( $primary_url ),
		esc_html( $primary_label )
	);
	$items .= '<ul class="absolute top-full right-0 w-48 rounded-xl '
		. 'shadow-2xl py-2 z-50 hidden group-hover:block '
		. 'group-focus-within:block account-submenu">';

	$items .= sprintf(
		'<li><a href="%1$s" class="block px-4 py-2 text-sm '
		. 'text-gray-400 hover:text-white hover:bg-slate-500/10">%2$s</a></li>',
		esc_url( admin_url( 'profile.php' ) ),
		esc_html__( 'Профиль', 'scam-dev' )
	);

	if ( current_user_can( 'manage_options' ) ) {
		$items .= sprintf(
			'<li><a href="%1$s" class="block px-4 py-2 text-sm '
			. 'text-gray-400 hover:text-white hover:bg-slate-500/10">%2$s</a></li>',
			esc_url( admin_url() ),
			esc_html__( 'Консоль', 'scam-dev' )
		);
		$items .= sprintf(
			'<li><a href="%1$s" class="block px-4 py-2 text-sm '
			. 'text-gray-400 hover:text-white hover:bg-slate-500/10">%2$s</a></li>',
			esc_url( admin_url( 'customize.php' ) ),
			esc_html__( 'Настроить', 'scam-dev' )
		);
	}

	$items .= sprintf(
		'<li><a href="%1$s" class="block px-4 py-2 text-sm '
		. 'text-coral-400 hover:text-white hover:bg-slate-500/10">%2$s</a></li>',
		esc_url( wp_logout_url( home_url( '/' ) ) ),
		esc_html__( 'Выйти', 'scam-dev' )
	);
	$items .= '</ul></li>';

	return $items;
}
add_filter( 'wp_nav_menu_items', 'scam_dev_add_account_menu_item', 10, 2 );
