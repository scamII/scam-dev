<?php
get_header();
?>

<section class="relative min-h-[90vh] flex items-center overflow-hidden">
	<div class="grid-bg absolute inset-0"></div>
	<div class="particles absolute inset-0" id="particles"></div>

	<div class="absolute top-1/4 left-1/4 w-96 h-96 bg-coral-500/5 rounded-full blur-[120px]"></div>
	<div class="absolute bottom-1/3 right-1/4 w-72 h-72 bg-coral-500/5 rounded-full blur-[100px]"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 w-full">
        <div class="flex justify-center mb-4">
			<?php
			$hero_svg = scam_dev_hero_svg_url();
			if ( $hero_svg ) :
				?>
				<img src="<?php echo esc_url( $hero_svg ); ?>" class="w-full max-w-2xl h-auto opacity-50" alt="" loading="eager">
			<?php else : ?>
			<svg class="w-full max-w-2xl h-auto opacity-50" viewBox="0 0 480 200" fill="none" xmlns="http://www.w3.org/2000/svg">
				<defs>
					<linearGradient id="g1" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#ff5743" stop-opacity="0.12"/><stop offset="100%" stop-color="#ff5743" stop-opacity="0.03"/></linearGradient>
					<linearGradient id="g2" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#fff" stop-opacity="0.04"/><stop offset="100%" stop-color="#fff" stop-opacity="0"/></linearGradient>
					<filter id="glow"><feGaussianBlur stdDeviation="2" result="blur"/><feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
					<linearGradient id="horseGrad" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#ff5743" stop-opacity="0.08"/><stop offset="100%" stop-color="#ff5743" stop-opacity="0.02"/></linearGradient>
				</defs>
				<style>
					@keyframes blink1 { 0%,100%{opacity:1} 50%{opacity:0.2} }
					@keyframes blink2 { 0%,100%{opacity:0.3} 50%{opacity:1} }
					@keyframes blink3 { 0%,100%{opacity:0.8} 30%{opacity:0.1} 60%{opacity:1} }
					@keyframes dashmove { to { stroke-dashoffset: -20; } }
					.led1 { animation: blink1 2s infinite; }
					.led2 { animation: blink2 1.5s infinite; }
					.led3 { animation: blink3 3s infinite; }
					.flow { stroke-dasharray: 4 6; animation: dashmove 1s linear infinite; }
				</style>
				<rect x="30" y="20" width="90" height="130" rx="6" stroke="#ff5743" stroke-width="2" fill="url(#g1)"/>
				<rect x="30" y="20" width="90" height="130" rx="6" fill="url(#g2)"/>
				<rect x="40" y="30" width="70" height="16" rx="3" fill="#ff5743" fill-opacity="0.2"/>
				<rect x="40" y="54" width="70" height="8" rx="2" fill="#e8e8e8" fill-opacity="0.04"/>
				<rect x="40" y="68" width="70" height="8" rx="2" fill="#e8e8e8" fill-opacity="0.04"/>
				<rect x="40" y="82" width="70" height="8" rx="2" fill="#e8e8e8" fill-opacity="0.04"/>
				<rect x="40" y="96" width="45" height="10" rx="2" fill="#ff9920" fill-opacity="0.25"/>
				<circle cx="100" cy="101" r="4" fill="#22c55e" fill-opacity="0.8" filter="url(#glow)" class="led1"/>
				<circle cx="110" cy="45" r="2.5" fill="#ff9920" fill-opacity="0.6" class="led2"/>
				<circle cx="110" cy="65" r="2.5" fill="#22c55e" fill-opacity="0.5" class="led3"/>

				<rect x="150" y="35" width="90" height="140" rx="6" stroke="#ff5743" stroke-width="2" fill="url(#g1)"/>
				<rect x="150" y="35" width="90" height="140" rx="6" fill="url(#g2)"/>
				<rect x="162" y="48" width="66" height="10" rx="2" fill="#ff5743" fill-opacity="0.16"/>
				<rect x="162" y="66" width="66" height="8" rx="2" fill="#e8e8e8" fill-opacity="0.04"/>
				<rect x="162" y="80" width="66" height="8" rx="2" fill="#e8e8e8" fill-opacity="0.04"/>
				<rect x="162" y="94" width="66" height="8" rx="2" fill="#e8e8e8" fill-opacity="0.04"/>
				<rect x="162" y="108" width="66" height="8" rx="2" fill="#e8e8e8" fill-opacity="0.04"/>
				<rect x="162" y="122" width="66" height="8" rx="2" fill="#e8e8e8" fill-opacity="0.04"/>
				<circle cx="195" cy="85" r="3" fill="#22c55e" fill-opacity="0.5" filter="url(#glow)" class="led2"/>
				<circle cx="220" cy="60" r="2.5" fill="#ff9920" fill-opacity="0.6" class="led1"/>

				<rect x="270" y="50" width="80" height="110" rx="6" stroke="#ff5743" stroke-width="2" fill="url(#g1)"/>
				<rect x="270" y="50" width="80" height="110" rx="6" fill="url(#g2)"/>
				<rect x="282" y="62" width="56" height="10" rx="2" fill="#ff5743" fill-opacity="0.14"/>
				<rect x="282" y="80" width="56" height="8" rx="2" fill="#e8e8e8" fill-opacity="0.04"/>
				<rect x="282" y="94" width="56" height="8" rx="2" fill="#e8e8e8" fill-opacity="0.04"/>
				<rect x="282" y="108" width="56" height="8" rx="2" fill="#e8e8e8" fill-opacity="0.04"/>
				<circle cx="310" cy="86" r="3" fill="#ff9920" fill-opacity="0.6" filter="url(#glow)" class="led3"/>

				<rect x="375" y="65" width="65" height="80" rx="6" stroke="#ff5743" stroke-width="1.5" stroke-dasharray="4 4" fill="url(#g1)"/>
				<rect x="385" y="78" width="45" height="8" rx="2" fill="#e8e8e8" fill-opacity="0.03"/>
				<rect x="385" y="92" width="45" height="8" rx="2" fill="#e8e8e8" fill-opacity="0.03"/>

				<line x1="120" y1="85" x2="148" y2="90" stroke="#ff5743" stroke-width="1.5" class="flow"/>
				<line x1="120" y1="115" x2="148" y2="110" stroke="#ff9920" stroke-width="1.5" class="flow" style="animation-delay:0.3s"/>
				<line x1="240" y1="95" x2="268" y2="100" stroke="#ff5743" stroke-width="1.5" class="flow" style="animation-delay:0.6s"/>
				<line x1="350" y1="105" x2="373" y2="105" stroke="#ff9920" stroke-width="1.5" class="flow" style="animation-delay:0.9s"/>
			</svg><?php endif; ?>
		</div>
		<div class="max-w-3xl">
			<div class="status-bar mb-8">
				<span class="status-dot"></span>
				<span>SYS.ONLINE</span>
				<span class="text-white/20">|</span>
				<span>UPTIME: <?php echo esc_html( human_time_diff( strtotime( get_bloginfo( 'launch_date' ) ?: '2025-01-01' ), time() ) ); ?></span>
			</div>

			<h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold leading-none tracking-tight text-balance">
				<span class="text-white"><?php bloginfo( 'name' ); ?></span>
				<br>
				<span class="text-gradient"><?php bloginfo( 'description' ); ?></span>
			</h1>

			<div class="mt-8 terminal-line text-sm md:text-base">
				<span class="text-coral-500/50">$</span>
				<span class="cursor-blink">exploring homelab. infra. matrix. horses</span>
			</div>

			<div class="mt-10 flex flex-wrap gap-4">
				<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="btn-primary text-base px-8 py-3.5 rounded-xl">
					<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
					<?php esc_html_e( 'Читать блог', 'scam-dev' ); ?>
				</a>
				<a href="#features" class="btn-outline text-base px-8 py-3.5 rounded-xl">
					<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
					<?php esc_html_e( 'Подробнее', 'scam-dev' ); ?>
				</a>
			</div>
		</div>
	</div>

	<div class="absolute bottom-8 left-1/2 -translate-x-1/2">
		<svg class="w-6 h-6 text-white/20 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
		</svg>
	</div>
</section>

<div class="relative z-10 -mt-16">
	<div class="max-w-4xl mx-auto px-4 sm:px-6">
		<div class="glass p-6 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
			<?php
			$count_posts = wp_count_posts()->publish;
			$count_pages = wp_count_posts( 'page' )->publish;
			?>
			<div>
				<div class="text-3xl font-extrabold text-white"><?php echo intval( $count_posts ); ?></div>
				<div class="text-xs text-gray-400 mt-1 uppercase tracking-wider">Articles</div>
			</div>
			<div>
				<div class="text-3xl font-extrabold text-white"><?php echo intval( $count_pages ); ?></div>
				<div class="text-xs text-gray-400 mt-1 uppercase tracking-wider">Pages</div>
			</div>
			<div>
				<div class="text-3xl font-extrabold text-white">24/7</div>
				<div class="text-xs text-gray-400 mt-1 uppercase tracking-wider">Uptime</div>
			</div>
			<div>
				<div class="text-3xl font-extrabold text-coral-400">∞</div>
				<div class="text-xs text-gray-400 mt-1 uppercase tracking-wider">Curiosity</div>
			</div>
		</div>
	</div>
</div>

<section id="features" class="pt-20 md:pt-28 pb-16 md:pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
			<span class="badge-coral mb-4">SERVICES</span>
			<h2 class="section-title mt-4">
				<?php esc_html_e( 'Чем я занимаюсь', 'scam-dev' ); ?>
			</h2>
			<div class="divider mx-auto mt-5"></div>
		</div>

		<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
			<?php
			$features = array(
				array(
					'tag'   => 'badge-coral',
					'icon'  => '<svg class="w-6 h-6 text-coral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>',
					'title' => __( 'Homelab и Инфра', 'scam-dev' ),
					'text'  => __( 'Кластеры Proxmox, сегментация VLAN, Docker/Podman, Nginx reverse proxy, WireGuard VPN. Построение надёжной self-hosted инфраструктуры.', 'scam-dev' ),
				),
				array(
					'tag'   => 'badge-coral',
					'icon'  => '<svg class="w-6 h-6 text-coral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
					'title' => __( 'Разработка', 'scam-dev' ),
					'text'  => __( 'Темы WordPress, кастомные плагины, микросервисы на Go, автоматизация Ansible. Чистый код на PHP, Golang, Bash и TypeScript.', 'scam-dev' ),
				),
				array(
					'tag'   => 'badge-coral',
					'icon'  => '<svg class="w-6 h-6 text-coral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.858 15.355-5.858 21.213 0"/></svg>',
					'title' => __( 'Matrix и Коммуникации', 'scam-dev' ),
					'text'  => __( 'Matrix-сервер, мосты, федеративные коммуникации. Безопасная инфраструктура обмена сообщениями для self-hosted сообществ.', 'scam-dev' ),
				),
			);

			foreach ( $features as $f ) :
				?>
				<div class="glass glass-hover p-8 group">
					<div class="w-12 h-12 rounded-xl bg-coral-500/10 flex items-center justify-center mb-5 group-hover:bg-coral-500/15 transition-colors">
                        <?php echo wp_kses_post( $f['icon'] ); ?>
					</div>
					<h3 class="text-xl font-bold text-white mb-3">
						<?php echo esc_html( $f['title'] ); ?>
					</h3>
					<p class="text-gray-400 leading-relaxed text-sm">
						<?php echo esc_html( $f['text'] ); ?>
					</p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="pb-16 md:pb-20">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="text-center mb-10">
			<span class="badge-coral mb-4">STACK</span>
			<h2 class="section-title mt-4">
				<?php esc_html_e( 'Инструменты и технологии', 'scam-dev' ); ?>
			</h2>
		</div>
		<div class="flex flex-wrap justify-center gap-3 max-w-3xl mx-auto">
			<?php
			$stack = array( 'Proxmox', 'Keenetic', 'Docker', 'Podman', 'Traefik', 'Nginx', 'WireGuard', 'Matrix', 'Element', 'PHP', 'WordPress', 'Golang', 'TypeScript', 'Bash', 'Ansible', 'Grafana', 'Prometheus', 'MariaDB', 'Redis', 'Cloudflare', 'Linux', 'LXC' );
			foreach ( $stack as $tech ) :
				?>
				<span class="px-4 py-2 glass text-sm text-gray-300 hover:text-white hover:border-coral-500/30 transition-all cursor-default">
					<?php echo esc_html( $tech ); ?>
				</span>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="pb-16 md:pb-20">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
			<div>
				<span class="badge-coral mb-4">BLOG</span>
				<h2 class="section-title mt-4">
					<?php esc_html_e( 'Последние статьи', 'scam-dev' ); ?>
				</h2>
			</div>
			<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"
				class="mt-4 md:mt-0 text-sm font-medium text-coral-400 hover:text-coral-300 transition-colors">
				<?php esc_html_e( 'Все записи', 'scam-dev' ); ?> →
			</a>
		</div>

		<div class="posts-grid cols-3">
			<?php
			$latest = new WP_Query(
				array(
					'posts_per_page'      => 3,
					'post_status'         => 'publish',
					'ignore_sticky_posts' => true,
				)
			);

			if ( $latest->have_posts() ) :
				while ( $latest->have_posts() ) :
					$latest->the_post();
					?>
					<article class="card group">
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" class="block overflow-hidden">
								<?php
								the_post_thumbnail(
									'medium_large',
									array(
										'class'   => 'w-full h-48 object-cover group-hover:scale-105 transition-transform duration-700',
										'loading' => 'lazy',
									)
								);
								?>
							</a>
						<?php endif; ?>
						<div class="card-body">
							<div class="flex items-center gap-3 text-xs text-gray-500 mb-3">
								<time datetime="<?php echo get_the_date( 'c' ); ?>"><?php echo get_the_date(); ?></time>
								<?php
								$cats = get_the_category();
								if ( $cats ) :
									?>
									<span class="text-slate-500/20">|</span>
									<span class="text-coral-400/80">
										<?php echo esc_html( $cats[0]->name ); ?>
									</span>
								<?php endif; ?>
							</div>
							<h3 class="text-lg font-bold text-white mb-2 group-hover:text-coral-400 transition-colors">
								<a href="<?php the_permalink(); ?>" class="hover:no-underline">
									<?php the_title(); ?>
								</a>
							</h3>
							<p class="text-gray-500 text-sm leading-relaxed line-clamp-2">
								<?php echo esc_html( get_the_excerpt() ); ?>
							</p>
						</div>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
			endif;
			?>
		</div>
	</div>
</section>

<section class="horses-section py-16 md:py-20">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
			<div>
				<span class="badge-amber mb-4">HORSES</span>
				<h2 class="section-title mt-4">
					<?php esc_html_e( 'Больше чем технологии', 'scam-dev' ); ?>
				</h2>
				<p class="text-gray-400 leading-relaxed mt-4 text-lg">
					<?php esc_html_e( 'Когда не в терминале — в конюшне. Лошади учат терпению, дисциплине и связи — ценности, которые отражаются в каждой строке кода.', 'scam-dev' ); ?>
				</p>
				<div class="mt-6">
					<a href="#" class="btn-outline text-base px-6 py-3 rounded-xl">
						<?php esc_html_e( 'Подробнее о лошадях', 'scam-dev' ); ?> →
					</a>
				</div>
			</div>
			<div class="glass p-1">
				<div class="aspect-[4/3] rounded-lg bg-gradient-to-br from-orange-900/30 via-orange-800/20 to-transparent flex items-center justify-center">
					<svg class="w-24 h-24 text-orange-700/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.5" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z"/>
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.5" d="M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925 3.546 5.974 5.974 0 01-2.133-1A3.75 3.75 0 0012 18z"/>
					</svg>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="py-20 md:py-28 text-center">
	<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
		<h2 class="text-3xl md:text-5xl font-extrabold text-white mb-6 text-balance">
			<?php esc_html_e( 'Давайте строить', 'scam-dev' ); ?>
		</h2>
		<p class="text-xl text-gray-400 mb-10 max-w-xl mx-auto">
			<?php esc_html_e( 'Homelab. Инфраструктура. Код. Лошади. Всегда учусь, всегда строю.', 'scam-dev' ); ?>
		</p>
		<div class="flex flex-wrap justify-center gap-4">
			<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="btn-primary text-base px-8 py-3.5 rounded-xl">
				<?php esc_html_e( 'Читать блог', 'scam-dev' ); ?>
			</a>
			<a href="#" class="btn-secondary text-base px-8 py-3.5 rounded-xl">
				<?php esc_html_e( 'Связаться', 'scam-dev' ); ?>
			</a>
		</div>
	</div>
</section>

<?php get_footer(); ?>

<script>
(function() {
	var p = document.getElementById('particles');
	if (!p) return;
	for (var i = 0; i < 30; i++) {
		var d = document.createElement('div');
		d.className = 'particle';
		d.style.left = Math.random() * 100 + '%';
		d.style.animationDelay = Math.random() * 6 + 's';
		d.style.animationDuration = (4 + Math.random() * 4) + 's';
		p.appendChild(d);
	}
})();
</script>
