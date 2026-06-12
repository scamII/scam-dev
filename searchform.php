<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="w-full">
		<span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'scam-dev' ); ?></span>
		<input type="search" class="search-field"
				placeholder="<?php esc_attr_e( 'Search ...', 'scam-dev' ); ?>"
				value="<?php echo get_search_query(); ?>" name="s"/>
	</label>
	<button type="submit" class="search-submit"
			aria-label="<?php esc_attr_e( 'Search', 'scam-dev' ); ?>">
		<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
					d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
		</svg>
	</button>
</form>
