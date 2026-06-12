<?php
get_header();

while ( have_posts() ) :
	the_post();
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
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="max-w-4xl mx-auto px-4 sm:px-6 -mt-8 relative z-10">
				<?php
				the_post_thumbnail(
					'large',
					array(
						'class'   => 'w-full h-auto rounded-2xl shadow-2xl shadow-black/50',
						'loading' => 'eager',
					)
				);
				?>
			</div>
		<?php endif; ?>

		<div class="max-w-4xl mx-auto px-4 sm:px-6 py-10 md:py-16">
			<?php scam_dev_breadcrumbs(); ?>
			<div class="prose prose-lg max-w-none">
				<?php the_content(); ?>
			</div>
		</div>
	</article>

	<?php
endwhile;

get_footer();
