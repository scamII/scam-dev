<?php
/**
 * Front page template.
 *
 * @package Scam_Dev
 */

get_header();

$posts_page_id  = (int) get_option( 'page_for_posts' );
$blog_url       = $posts_page_id ? get_permalink( $posts_page_id ) : home_url( '/blog/' );
$contact_email  = sanitize_email( get_theme_mod( 'contact_email', get_option( 'admin_email' ) ) );
$contact_url    = $contact_email ? 'mailto:' . $contact_email : home_url( '/' );
$horses         = get_category_by_slug( 'loshadi' );
$horses_link    = $horses ? get_category_link( $horses->term_id ) : '';
$horses_url     = $horses_link && ! is_wp_error( $horses_link )
	? $horses_link
	: $blog_url;
$hero_media     = scam_dev_get_hero_media();
?>

<section class="relative min-h-[82vh] flex items-center overflow-hidden">
	<div class="grid-bg absolute inset-0" aria-hidden="true"></div>
	<div class="particles absolute inset-0" id="particles" aria-hidden="true"></div>

	<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full">
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
			<div>
				<div class="status-bar mb-8">
					<span class="status-dot" aria-hidden="true"></span>
					<span>SYS.ONLINE</span>
					<span class="text-white/20" aria-hidden="true">|</span>
					<span><?php esc_html_e( 'STATUS: OK', 'scam-dev' ); ?></span>
				</div>

				<h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold leading-none tracking-tight text-balance">
					<span class="text-white"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
					<br>
					<span class="text-gradient"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></span>
				</h1>

				<p class="mt-8 terminal-line text-sm md:text-base">
					<span class="text-coral-500/50" aria-hidden="true">$</span>
					<span>exploring homelab. infra. matrix. horses</span>
				</p>

				<div class="mt-10 flex flex-wrap gap-4">
					<a href="<?php echo esc_url( $blog_url ); ?>"
						class="btn-primary text-base px-8 py-3.5 rounded-xl">
						<?php esc_html_e( 'Читать блог', 'scam-dev' ); ?>
					</a>
					<a href="#features"
						class="btn-outline text-base px-8 py-3.5 rounded-xl">
						<?php esc_html_e( 'Подробнее', 'scam-dev' ); ?>
					</a>
				</div>
			</div>

			<div class="flex justify-center">
				<?php if ( $hero_media ) : ?>
					<img src="<?php echo esc_url( $hero_media['url'] ); ?>"
						alt="<?php echo esc_attr( $hero_media['alt'] ); ?>"
						class="w-full max-w-2xl h-auto opacity-80"
						loading="eager" fetchpriority="high">
				<?php else : ?>
					<svg class="w-full max-w-xl h-auto opacity-70"
						viewBox="0 0 480 260"
						fill="none"
						xmlns="http://www.w3.org/2000/svg"
						role="img"
						aria-label="<?php esc_attr_e( 'Схема домашней инфраструктуры', 'scam-dev' ); ?>">
						<rect x="30" y="35" width="120" height="185" rx="14"
							stroke="currentColor" stroke-width="3"/>
						<rect x="180" y="65" width="120" height="155" rx="14"
							stroke="currentColor" stroke-width="3"/>
						<rect x="330" y="95" width="120" height="125" rx="14"
							stroke="currentColor" stroke-width="3"/>
						<path d="M150 125H180M300 145H330"
							stroke="currentColor" stroke-width="3"
							stroke-dasharray="8 8"/>
						<circle cx="90" cy="80" r="8" fill="currentColor"/>
						<circle cx="240" cy="105" r="8" fill="currentColor"/>
						<circle cx="390" cy="130" r="8" fill="currentColor"/>
					</svg>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<section id="features" class="py-20 md:py-28">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="text-center mb-14">
			<span class="badge-coral">SERVICES</span>
			<h2 class="section-title mt-4">
				<?php esc_html_e( 'Чем я занимаюсь', 'scam-dev' ); ?>
			</h2>
			<div class="divider mx-auto mt-5"></div>
		</div>

		<?php
		$features = array(
			array(
				'title' => __( 'Homelab и инфраструктура', 'scam-dev' ),
				'text'  => __( 'Proxmox, VLAN, контейнеры, reverse proxy, VPN, мониторинг и отказоустойчивые self-hosted сервисы.', 'scam-dev' ),
			),
			array(
				'title' => __( 'Разработка', 'scam-dev' ),
				'text'  => __( 'Темы и плагины WordPress, автоматизация, сервисы на Go и TypeScript, надёжные процессы сборки и доставки.', 'scam-dev' ),
			),
			array(
				'title' => __( 'Matrix и коммуникации', 'scam-dev' ),
				'text'  => __( 'Федеративные коммуникации, управление Matrix-сервером, безопасная регистрация и эксплуатация.', 'scam-dev' ),
			),
		);
		?>
		<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
			<?php foreach ( $features as $feature ) : ?>
				<article class="glass glass-hover p-8">
					<h3 class="text-xl font-bold text-white mb-3">
						<?php echo esc_html( $feature['title'] ); ?>
					</h3>
					<p class="text-gray-400 leading-relaxed text-sm">
						<?php echo esc_html( $feature['text'] ); ?>
					</p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="pb-16 md:pb-20">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
			<div>
				<span class="badge-coral">BLOG</span>
				<h2 class="section-title mt-4">
					<?php esc_html_e( 'Последние статьи', 'scam-dev' ); ?>
				</h2>
			</div>
			<a href="<?php echo esc_url( $blog_url ); ?>"
				class="mt-4 md:mt-0 text-sm font-medium text-coral-400 hover:text-coral-300 transition-colors">
				<?php esc_html_e( 'Все записи', 'scam-dev' ); ?> &rarr;
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

			while ( $latest->have_posts() ) :
				$latest->the_post();
				?>
				<article <?php post_class( 'card group' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a href="<?php echo esc_url( get_permalink() ); ?>" class="block overflow-hidden">
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
						<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"
							class="text-xs text-gray-500">
							<?php echo esc_html( get_the_date() ); ?>
						</time>
						<h3 class="text-lg font-bold text-white mt-3 mb-2">
							<a href="<?php echo esc_url( get_permalink() ); ?>">
								<?php echo esc_html( get_the_title() ); ?>
							</a>
						</h3>
						<p class="text-gray-500 text-sm leading-relaxed line-clamp-2">
							<?php echo esc_html( get_the_excerpt() ); ?>
						</p>
					</div>
				</article>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</div>
	</div>
</section>

<section class="horses-section py-16 md:py-20">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
			<div>
				<span class="badge-orange">HORSES</span>
				<h2 class="section-title mt-4">
					<?php esc_html_e( 'Больше, чем технологии', 'scam-dev' ); ?>
				</h2>
				<p class="text-gray-400 leading-relaxed mt-4 text-lg">
					<?php esc_html_e( 'Лошади учат терпению, дисциплине и вниманию — качествам, которые важны и в инженерной работе.', 'scam-dev' ); ?>
				</p>
				<a href="<?php echo esc_url( $horses_url ); ?>"
					class="btn-outline inline-flex mt-6 text-base px-6 py-3 rounded-xl">
					<?php esc_html_e( 'Материалы о лошадях', 'scam-dev' ); ?> &rarr;
				</a>
			</div>
			<div class="glass p-8 text-center">
				<span class="text-7xl" aria-hidden="true">♞</span>
			</div>
		</div>
	</div>
</section>

<section class="py-20 md:py-28 text-center">
	<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
		<h2 class="text-3xl md:text-5xl font-extrabold text-white mb-6 text-balance">
			<?php esc_html_e( 'Давайте строить надёжно', 'scam-dev' ); ?>
		</h2>
		<p class="text-xl text-gray-400 mb-10 max-w-xl mx-auto">
			<?php esc_html_e( 'Инфраструктура, код и открытые знания без лишней магии.', 'scam-dev' ); ?>
		</p>

		<?php get_template_part( 'template-parts/donate-inline' ); ?>

		<div class="flex flex-wrap justify-center gap-4 mt-8">
			<a href="<?php echo esc_url( $blog_url ); ?>"
				class="btn-primary text-base px-8 py-3.5 rounded-xl">
				<?php esc_html_e( 'Читать блог', 'scam-dev' ); ?>
			</a>
			<a href="<?php echo esc_url( $contact_url ); ?>"
				class="btn-secondary text-base px-8 py-3.5 rounded-xl">
				<?php esc_html_e( 'Связаться', 'scam-dev' ); ?>
			</a>
		</div>
	</div>
</section>

<?php get_footer(); ?>
