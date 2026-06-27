<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>
		(function() {
			var t = localStorage.getItem('theme');
			if (t === 'light' || t === 'dark') {
				document.documentElement.setAttribute('data-theme', t)
			} else if (window.matchMedia('(prefers-color-scheme:light)').matches) {
				document.documentElement.setAttribute('data-theme', 'light')
			}
		})();
	</script>
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class('min-h-screen flex flex-col'); ?>>
	<div id="progress-bar-root"></div>
	<?php wp_body_open(); ?>

	<a class="skip-link screen-reader-text" href="#main-content">
		<?php esc_html_e('Перейти к содержимому', 'scam-dev'); ?>
	</a>

	<header class="sticky-header">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex justify-between items-center h-12 lg:h-14">
				<div class="flex-shrink-0 flex items-center gap-3">
					<a href="<?php echo esc_url(home_url('/')); ?>"
						class="text-xl font-extrabold text-white no-underline tracking-tight">
						<span class="text-coral-400">&gt;</span> <?php bloginfo('name'); ?>
					</a>
				</div>

				<div class="hidden lg:flex items-center gap-4">
					<nav aria-label="<?php esc_attr_e('Главное меню', 'scam-dev'); ?>">
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
					<div class="relative flex items-center">
						<button type="button" class="p-2 flex-shrink-0 transition-colors duration-300" style="color:var(--color-text-secondary)"
							onclick="var f=document.getElementById('hs-wrap');var i=document.getElementById('hs-input');
								if(f.style.width==='200px'){f.style.width='0';f.style.opacity='0';f.style.padding='0';setTimeout(()=>i.value='',300)}
								else{f.style.width='200px';f.style.opacity='1';f.style.padding='0 0 0 4px';setTimeout(()=>i.focus(),100)}"
							aria-label="Поиск"
							onmouseover="this.style.color='var(--color-text)'"
							onmouseout="this.style.color='var(--color-text-secondary)'">
							<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
							</svg>
						</button>
						<form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>"
							id="hs-wrap"
							class="absolute left-full top-1/2 -translate-y-1/2 transition-all duration-300 ease-in-out z-10"
							style="width:0;opacity:0;">
							<input type="search" name="s" id="hs-input"
								class="w-full max-w-[200px] py-2 px-4 text-sm rounded-full focus:outline-none"
								style="
									color: var(--color-text);
									border: 1.5px solid var(--color-accent);
								"
								placeholder="Поиск ..."
								value="<?php echo get_search_query(); ?>" />
						</form>
					</div>
					<div id="theme-toggle-root"></div>
				</div>

				<div class="lg:hidden flex items-center gap-2">
					<div id="theme-toggle-root-mobile"></div>
					<div id="burger-menu-root"></div>
				</div>
			</div>
		</div>
	</header>

	<div id="mobile-menu-html" class="hidden">
		<div class="space-y-3">
			<div class="flex justify-end">
				<div id="theme-toggle-root-mobile-inner"></div>
			</div>
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
			<div class="pt-3 border-t" style="border-color:var(--color-border)">
				<div style="border:1.5px solid var(--color-accent);border-radius:9999px;padding:2px">
					<?php get_search_form(); ?>
				</div>
			</div>
		</div>
	</div>

	<main id="main-content" class="flex-1 pt-[72px]">

		<a href="https://tbank.ru/cf/2x90jZaUieT" target="_blank" rel="noopener" id="floating-donate"
			class="fixed flex items-center gap-2 z-40 rounded-full transition-all duration-300"
			style="right:1.5rem;top:50%;transform:translateY(-50%);padding:0.6rem 1.25rem;
				background:var(--color-accent);color:#19253b;font-weight:600;font-size:0.875rem;
				text-decoration:none;box-shadow:0 4px 20px rgba(255,87,67,0.35)"
			onmouseover="this.style.boxShadow='0 6px 28px rgba(255,87,67,0.5)';this.style.transform='translateY(-50%) scale(1.03)'"
			onmouseout="this.style.boxShadow='0 4px 20px rgba(255,87,67,0.35)';this.style.transform='translateY(-50%)'">
			<svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24" style="animation:pulse-donate 1.6s ease-in-out infinite">
				<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
			</svg>
			<span class="hidden sm:inline">Поддержать</span>
		</a>

		<style>
			@keyframes pulse-donate {

				0%,
				100% {
					transform: scale(1);
				}

				50% {
					transform: scale(1.15);
				}
			}
		</style>