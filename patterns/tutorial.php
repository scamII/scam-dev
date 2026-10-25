<?php
/**
 * Title: Туториал с кодом
 * Slug: scam-dev/tutorial
 * Categories: posts, text
 * Description: Статья-руководство: что понадобится, шаги с командами, проверка результата.
 * Keywords: туториал, инструкция, код, гайд
 * Post Types: post
 * Viewport Width: 800
 */
?>
<!-- wp:paragraph {"className":"text-lg"} -->
<p class="text-lg">В одном абзаце: какую задачу решаем и что получится в конце. Читатель должен понять, стоит ли ему читать дальше.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Что понадобится</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Окружение: Debian 13, доступ по SSH</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Пакеты, которые установим по ходу</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Примерное время: 15 минут</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-coral"} -->
<p class="badge-coral">Важно</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Что нужно сделать до начала: бэкап, свободное место, отключённые сервисы. Всё, что может пойти не так.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Шаг 1. Подготовка</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Что делаем и зачем.</p>
<!-- /wp:paragraph -->

<!-- wp:code -->
<pre class="wp-block-code"><code>sudo apt update
sudo apt install пакет</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Шаг 2. Настройка</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Правки конфигурации. Файл, что меняем, почему.</p>
<!-- /wp:paragraph -->

<!-- wp:code -->
<pre class="wp-block-code"><code>sudo nano /etc/example.conf

# было:
# option = 0
# стало:
option = 1</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Шаг 3. Проверка</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Как убедиться, что всё работает. Ожидаемый вывод.</p>
<!-- /wp:paragraph -->

<!-- wp:code -->
<pre class="wp-block-code"><code>systemctl status пример --no-pager
# Active: active (running)</code></pre>
<!-- /wp:code -->

<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-orange"} -->
<p class="badge-orange">Откат</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Как вернуть всё назад, если не подошло.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Итог</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Что получилось, что можно улучшить, на что обратить внимание.</p>
<!-- /wp:paragraph -->
