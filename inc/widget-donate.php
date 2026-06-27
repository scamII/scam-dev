<?php
if (! defined('ABSPATH')) {
	exit;
}

class Scam_Dev_Donate_Widget extends WP_Widget
{

	public function __construct()
	{
		parent::__construct(
			'scamdev_donate',
			'Поддержать проект',
			array('description' => 'Кнопка и QR-код для доната через Т-Банк')
		);
	}

	public function widget($args, $instance)
	{
		$title = ! empty($instance['title']) ? $instance['title'] : 'Поддержать проект';
		$text  = ! empty($instance['text']) ? $instance['text'] : 'Помогите серверу жить';

		$donate_url = 'https://tbank.ru/cf/2x90jZaUieT';

		echo $args['before_widget'];
		if ($title) {
			echo $args['before_title'] . esc_html($title) . $args['after_title'];
		}
?>
		<div class="glass" style="padding:1.25rem;text-align:center;border-left:3px solid var(--color-accent)">
			<p style="margin:0 0 1rem;color:var(--color-text-secondary);font-size:0.85rem;line-height:1.5">
				<?php echo esc_html($text); ?>
			</p>
			<img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($donate_url); ?>"
				alt="QR-код Т-Банк" width="130" height="130"
				style="display:block;margin:0 auto 1rem;border-radius:12px;background:#fff;padding:10px;box-shadow:0 4px 24px rgba(0,0,0,0.25)">
			<a href="<?php echo esc_url($donate_url); ?>" target="_blank" rel="noopener"
				style="display:inline-flex;align-items:center;justify-content:center;gap:6px;
						background:var(--color-accent,#ff5743);color:#19253b;font-weight:600;
						font-size:0.875rem;padding:0.625rem 1.75rem;border-radius:9999px;
						text-decoration:none;white-space:nowrap;transition:all 0.25s"
				onmouseover="this.style.boxShadow='0 0 24px color-mix(in srgb, var(--color-accent) 50%, transparent)';this.style.transform='translateY(-1px)'"
				onmouseout="this.style.boxShadow='none';this.style.transform='none'">
				<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
					<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
				</svg>
				<?php esc_html_e('Поддержать', 'scam-dev'); ?>
			</a>
			<p style="margin:0.75rem 0 0;font-size:0.7rem;color:var(--color-text-secondary);opacity:0.5">
				<?php esc_html_e('через Т-Банк', 'scam-dev'); ?>
			</p>
		</div>
	<?php
		echo $args['after_widget'];
	}

	public function form($instance)
	{
		$title = ! empty($instance['title']) ? $instance['title'] : '';
		$text  = ! empty($instance['text']) ? $instance['text'] : '';
	?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Заголовок:</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
				name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
				value="<?php echo esc_attr($title); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('text')); ?>">Текст:</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('text')); ?>"
				name="<?php echo esc_attr($this->get_field_name('text')); ?>" type="text"
				value="<?php echo esc_attr($text); ?>">
		</p>
<?php
	}

	public function update($new_instance, $old_instance)
	{
		$instance          = array();
		$instance['title'] = sanitize_text_field($new_instance['title']);
		$instance['text']  = sanitize_text_field($new_instance['text']);
		return $instance;
	}
}

add_action(
	'widgets_init',
	function () {
		register_widget('Scam_Dev_Donate_Widget');
	}
);
