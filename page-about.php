<?php
/*
Template Name: About
*/
get_header();
?>

<section class="relative overflow-hidden border-b border-slate-500/10">
	<div class="absolute inset-0 bg-gradient-to-b from-coral-500/5 to-transparent"></div>
	<div class="grid-bg absolute inset-0 opacity-50"></div>
	<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20 text-center">
		<h1 class="text-3xl md:text-5xl font-extrabold text-white leading-tight">
			<?php the_title(); ?>
		</h1>
	</div>
</section>

<article>
	<div class="max-w-4xl mx-auto px-4 sm:px-6 py-10 md:py-16">
		<?php scam_dev_breadcrumbs(); ?>

		<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
			<div class="md:col-span-1">
				<div class="glass p-6 text-center sticky top-24">
					<div class="w-24 h-24 mx-auto rounded-full bg-coral-500/10 flex items-center justify-center mb-4">
						<svg class="w-12 h-12 text-coral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
						</svg>
					</div>
					<h2 class="text-lg font-bold text-white mb-1"><?php bloginfo( 'name' ); ?></h2>
					<p class="text-gray-500 text-sm">Developer & Homelab Enthusiast</p>
					<div class="flex justify-center gap-3 mt-4">
						<a href="https://github.com" class="social-icon" aria-label="GitHub" target="_blank" rel="noopener">
							<svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
						</a>
						<a href="https://matrix.org" class="social-icon" aria-label="Matrix" target="_blank" rel="noopener">
							<svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M.632.55v22.9H2.28V24H0V0h2.28v.55H.632zm7.043 7.26v1.157h.033a3.312 3.312 0 011.117-1.024c.455-.246.973-.37 1.554-.37.795 0 1.478.17 2.05.51.574.34 1.02.81 1.34 1.41.322.6.483 1.294.483 2.083v6.315h-2.083v-5.87c0-.75-.175-1.322-.524-1.717-.35-.395-.84-.593-1.474-.593-.72 0-1.28.25-1.68.75-.4.498-.6 1.2-.6 2.104v5.326H5.81V7.81h1.865zm12.447 0v1.157h.033a3.312 3.312 0 011.117-1.024c.455-.246.973-.37 1.554-.37.795 0 1.478.17 2.05.51.574.34 1.02.81 1.34 1.41.322.6.483 1.294.483 2.083v6.315h-2.083v-5.87c0-.75-.175-1.322-.524-1.717-.35-.395-.84-.593-1.474-.593-.72 0-1.28.25-1.68.75-.4.498-.6 1.2-.6 2.104v5.326h-2.064V7.81h1.865z"/></svg>
						</a>
						<a href="<?php echo esc_url( home_url( '/feed/' ) ); ?>" class="social-icon" aria-label="RSS" target="_blank" rel="noopener">
							<svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M6.18 15.64a2.18 2.18 0 010 4.36 2.18 2.18 0 010-4.36M4 4.44A15.56 15.56 0 0119.56 20h-2.83A12.73 12.73 0 004 7.27V4.44m0 5.66a9.9 9.9 0 019.9 9.9h-2.83A7.07 7.07 0 004 12.93v-2.83z"/></svg>
						</a>
					</div>
				</div>
			</div>

			<div class="md:col-span-2">
				<div class="prose prose-lg max-w-none">
					<?php the_content(); ?>
				</div>
			</div>
		</div>

		<div class="glass p-8 mt-8">
			<h2 class="text-xl font-bold text-white mb-6">
				<span class="text-coral-400">#</span> <?php esc_html_e( 'Стек технологий', 'scam-dev' ); ?>
			</h2>
			<div class="flex flex-wrap gap-2">
				<?php
				$stack = array( 'Proxmox', 'Keenetic', 'Docker', 'Podman', 'Traefik', 'Nginx', 'WireGuard', 'Matrix', 'PHP', 'WordPress', 'Golang', 'TypeScript', 'Ansible', 'Grafana', 'Prometheus', 'MariaDB', 'Redis', 'Linux', 'LXC' );
				foreach ( $stack as $tech ) :
					?>
					<span class="px-3 py-1.5 glass text-sm text-gray-300"><?php echo esc_html( $tech ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</article>

<?php get_footer(); ?>
