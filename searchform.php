<?php
/**
 * Search form.
 *
 * @package Scam_Dev
 */
?>
<form role="search" method="get" class="search-form"
	action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="w-full">
		<span class="screen-reader-text">
			<?php esc_html_e( 'Поиск:', 'scam-dev' ); ?>
		</span>
		<input type="search"
			class="search-field"
			placeholder="<?php esc_attr_e( 'Поиск…', 'scam-dev' ); ?>"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			name="s">
	</label>
	<button type="submit"
		class="search-submit"
		aria-label="<?php esc_attr_e( 'Искать', 'scam-dev' ); ?>">
		<span aria-hidden="true">⌕</span>
	</button>
</form>
