<?php
/**
 * Comments template.
 *
 * @package Scam_Dev
 */

if ( post_password_required() ) {
	return;
}
?>

<section id="comments" class="comments-area mt-12">
	<?php if ( have_comments() ) : ?>
		<h2 class="text-xl font-bold text-white mb-6">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: number of comments. */
					_n(
						'%s комментарий',
						'%s комментариев',
						get_comments_number(),
						'scam-dev'
					),
					number_format_i18n( get_comments_number() )
				)
			);
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>

		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php
	if ( ! comments_open() && get_comments_number() ) :
		?>
		<p class="no-comments text-gray-500">
			<?php esc_html_e( 'Комментарии закрыты.', 'scam-dev' ); ?>
		</p>
	<?php endif; ?>

	<?php comment_form(); ?>
</section>
