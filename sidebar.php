<?php
/**
 * Sidebar template.
 *
 * @package Scam_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_active_sidebar( 'sidebar-1' ) ) :
	?>
	<aside class="w-80 shrink-0 space-y-4"
		aria-label="<?php esc_attr_e( 'Боковая панель', 'scam-dev' ); ?>">
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	</aside>
<?php endif; ?>
