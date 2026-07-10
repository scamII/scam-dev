<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class( 'min-h-screen flex flex-col' ); ?>>
<?php wp_body_open(); ?>

<div id="progress-bar-root"></div>

<a class="skip-link screen-reader-text" href="#main-content">
	<?php esc_html_e( 'Перейти к содержимому', 'scam-dev' ); ?>
</a>

<header class="sticky-header" id="site-header">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="flex justify-between items-center h-12 lg:h-14">
			<div class="flex-shrink-0 flex items-center gap-3">
				<?php if ( has_custom_logo() ) : ?>
					<div class="site-logo">
						<?php echo wp_kses_post( get_custom_logo() ); ?>
					</div>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"
						class="text-xl font-extrabold text-white no-underline tracking-tight">
						<span class="text-coral-400" aria-hidden="true">&gt;</span>
						<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
					</a>
				<?php endif; ?>
			</div>

			<div class="hidden lg:flex items-center gap-3">
				<nav aria-label="<?php esc_attr_e( 'Главное меню', 'scam-dev' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'menu_class'     => 'flex items-center gap-1',
							'container'      => false,
							'depth'          => 2,
							'fallback_cb'    => false,
							'walker'         => new Scam_Dev_Nav_Walker(),
						)
					);
					?>
				</nav>

				<div class="header-search">
					<button type="button"
						id="header-search-toggle"
						class="p-2 rounded-lg transition-colors hover:bg-slate-500/10"
						aria-controls="header-search-form"
						aria-expanded="false"
						aria-label="<?php esc_attr_e( 'Открыть поиск', 'scam-dev' ); ?>">
						<svg class="w-5 h-5" fill="none" stroke="currentColor"
							viewBox="0 0 24 24" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round"
								stroke-width="2"
								d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
						</svg>
					</button>
					<div id="header-search-form" class="header-search-panel" hidden>
						<?php get_search_form(); ?>
					</div>
				</div>

				<div id="theme-toggle-root"></div>
			</div>

			<div class="lg:hidden flex items-center gap-2">
				<div id="theme-toggle-root-mobile"></div>
				<button type="button"
					id="mobile-menu-toggle"
					class="p-2 rounded-lg transition-colors hover:bg-slate-500/10"
					aria-controls="mobile-menu-panel"
					aria-expanded="false"
					aria-label="<?php esc_attr_e( 'Открыть меню', 'scam-dev' ); ?>">
					<svg class="w-6 h-6 mobile-menu-open-icon" fill="none"
						stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round"
							stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
					</svg>
					<svg class="w-6 h-6 mobile-menu-close-icon" fill="none"
						stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"
						hidden>
						<path stroke-linecap="round" stroke-linejoin="round"
							stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
					</svg>
				</button>
			</div>
		</div>
	</div>
</header>

<div id="mobile-menu-panel"
	class="mobile-menu-panel"
	role="dialog"
	aria-modal="true"
	aria-label="<?php esc_attr_e( 'Мобильное меню', 'scam-dev' ); ?>"
	hidden>
	<div class="mobile-menu-backdrop" data-mobile-menu-close></div>
	<div class="mobile-menu-content" tabindex="-1">
		<nav aria-label="<?php esc_attr_e( 'Мобильное главное меню', 'scam-dev' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'menu_class'     => 'space-y-1',
					'container'      => false,
					'depth'          => 2,
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>
		<div class="pt-4 mt-4 border-t border-slate-500/10">
			<?php get_search_form(); ?>
		</div>
	</div>
</div>

<main id="main-content" class="flex-1 pt-[72px]">
