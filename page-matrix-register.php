<?php

/**
 * Template Name: Matrix Registration
 * Description: Страница регистрации в Matrix-чате.
 *
 * @package Scam_Dev
 */

get_header();

$form_errors = get_transient('matrix_register_errors');
$success     = get_transient('matrix_register_success');
$old_user    = get_transient('matrix_register_username');

delete_transient('matrix_register_errors');
delete_transient('matrix_register_success');
delete_transient('matrix_register_username');

$form_errors = is_array($form_errors) ? $form_errors : array();
?>

<section class="relative overflow-hidden border-b border-slate-500/10">
	<div class="absolute inset-0 bg-gradient-to-b from-coral-500/5 to-transparent"></div>
	<div class="grid-bg absolute inset-0 opacity-50"></div>

	<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20 text-center">
		<h1 class="text-3xl md:text-5xl font-extrabold text-white leading-tight">
			<?php echo esc_html(get_the_title()); ?>
		</h1>
		<?php if ($success) : ?>
			<p class="mt-4 text-green-400 text-lg font-medium">
				✅ Регистрация прошла успешно! Теперь вы можете войти в Matrix-чат.
			</p>
		<?php else : ?>
			<p class="mt-4 text-gray-400 text-lg max-w-2xl mx-auto">
				Создайте учётную запись на нашем Matrix-сервере для общения в чатах и конференциях.
			</p>
		<?php endif; ?>
	</div>
</section>

<article class="max-w-2xl mx-auto px-4 sm:px-6 py-10 md:py-16">
	<?php if (! empty($form_errors)) : ?>
		<div class="mb-8 p-4 rounded-xl border border-red-500/30 bg-red-500/10 text-red-300 text-sm space-y-2">
			<?php foreach ($form_errors as $form_error) : ?>
				<p class="flex items-start gap-2">
					<svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
					</svg>
					<?php echo esc_html($form_error); ?>
				</p>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ($success) : ?>
		<div class="glass p-8 rounded-2xl text-center space-y-6">
			<div class="mx-auto w-16 h-16 rounded-full bg-green-500/20 flex items-center justify-center">
				<svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
				</svg>
			</div>
			<h2 class="text-2xl font-bold text-white">
				Регистрация завершена!
			</h2>
			<p class="text-gray-400">
				Ваша учётная запись на Matrix-сервере создана. Используйте указанные имя пользователя и пароль для входа в Matrix-клиент.
			</p>
			<div class="flex flex-col sm:flex-row gap-3 justify-center pt-4">
				<a href="https://chat.scam-dev.ru"
					target="_blank" rel="noopener"
					class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-green-600 hover:bg-green-500 text-white font-semibold rounded-xl transition-colors">
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
					</svg>
					Перейти в чат
				</a>
			</div>
		</div>
	<?php else : ?>
		<form method="post" action="" class="glass p-8 rounded-2xl space-y-6" novalidate>
			<?php wp_nonce_field('matrix_register'); ?>

			<div>
				<label for="mx_username" class="block text-sm font-semibold text-gray-300 mb-2">
					Имя пользователя
				</label>
				<div class="relative">
					<span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm select-none pointer-events-none">
						@
					</span>
					<input
						type="text"
						name="mx_username"
						id="mx_username"
						value="<?php echo esc_attr($old_user); ?>"
						required
						minlength="3"
						maxlength="64"
						pattern="[a-zA-Z0-9._\-]+"
						placeholder="ваше_имя"
						autocomplete="username"
						dir="ltr"
						class="mx-input w-full px-4 py-3 bg-slate-500/10 border border-slate-500/20 rounded-xl text-white placeholder:text-gray-600 focus:outline-none focus:border-coral-500/50 transition-colors"
						style="padding-left:2.25rem" />
				</div>
				<p class="mt-1.5 text-xs text-gray-500">
					Только латинские буквы, цифры, точки, дефисы и подчёркивания.
					<br>После регистрации ваш Matrix ID будет: <code class="text-coral-400">@ваше_имя:<?php echo esc_html(wp_parse_url(defined('MATRIX_HOMESERVER_URL') ? MATRIX_HOMESERVER_URL : 'example.com', PHP_URL_HOST) ? wp_parse_url(defined('MATRIX_HOMESERVER_URL') ? MATRIX_HOMESERVER_URL : 'example.com', PHP_URL_HOST) : 'chat.scam-dev.ru'); ?></code>
				</p>
			</div>

			<div>
				<label for="mx_password" class="block text-sm font-semibold text-gray-300 mb-2">
					Пароль
				</label>
				<div class="relative">
					<input
						type="password"
						name="mx_password"
						id="mx_password"
						required
						minlength="8"
						maxlength="128"
						placeholder="Не менее 8 символов"
						autocomplete="new-password"
						class="mx-input w-full px-4 py-3 bg-slate-500/10 border border-slate-500/20 rounded-xl text-white placeholder:text-gray-600 focus:outline-none focus:border-coral-500/50 transition-colors" />
					<button type="button"
						class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 p-1"
						onclick="var p=this.parentElement.querySelector('input');p.type=p.type==='password'?'text':'password';"
						aria-label="Показать/скрыть пароль">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
						</svg>
					</button>
				</div>
			</div>

			<div>
				<label for="mx_password_confirm" class="block text-sm font-semibold text-gray-300 mb-2">
					Подтверждение пароля
				</label>
				<input
					type="password"
					name="mx_password_confirm"
					id="mx_password_confirm"
					required
					minlength="8"
					maxlength="128"
					placeholder="Повторите пароль"
					autocomplete="new-password"
					class="mx-input w-full px-4 py-3 bg-slate-500/10 border border-slate-500/20 rounded-xl text-white placeholder:text-gray-600 focus:outline-none focus:border-coral-500/50 transition-colors" />
			</div>

			<div class="flex items-start gap-3 pt-2">
				<input
					type="checkbox"
					name="mx_tos"
					id="mx_tos"
					required
					class="mt-1 w-4 h-4 rounded border-slate-500/30 bg-slate-500/10 text-coral-500 focus:ring-coral-500 focus:ring-offset-0" />
				<label for="mx_tos" class="text-sm text-gray-400">
					Я принимаю
					<a href="/tos/" target="_blank" class="text-coral-400 hover:text-coral-300 underline">условия использования</a>
					и
					<a href="/privacy-policy/" target="_blank" class="text-coral-400 hover:text-coral-300 underline">политику конфиденциальности</a>
				</label>
			</div>

			<button
				type="submit"
				name="matrix_register"
				value="1"
				class="w-full py-3.5 bg-coral-600 hover:bg-coral-500 text-white font-semibold rounded-xl transition-colors text-lg">
				Зарегистрироваться
			</button>

			<p class="text-center text-sm text-gray-500 pt-2">
				Уже есть аккаунт?
				<a href="https://chat.scam-dev.ru" target="_blank" rel="noopener" class="text-coral-400 hover:text-coral-300 underline">
					Войти в чат
				</a>
			</p>
		</form>
	<?php endif; ?>
</article>

<?php
get_footer();
