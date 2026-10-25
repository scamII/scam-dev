<?php
/**
 * Title: Ошибка и решение
 * Slug: scam-dev/error-fix
 * Categories: posts, text
 * Description: Разбор проблемы: симптом, причина, решение, проверка.
 * Keywords: ошибка, баг, решение, разбор, troubleshooting
 * Post Types: post
 * Viewport Width: 800
 */
?>
<!-- wp:paragraph {"className":"text-lg"} -->
<p class="text-lg">Что сломалось и в каких условиях. Если читатель ищет по тексту ошибки — она должна быть в первых строках.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Симптом</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Что происходит. Дословный текст ошибки в логе или консоли:</p>
<!-- /wp:paragraph -->

<!-- wp:code -->
<pre class="wp-block-code"><code>ошибка: текст сообщения целиком
  at module.js:42:13</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Причина</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Почему так вышло. Механизм, а не догадка. Если причин несколько — перечислите их.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Решение</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Что сделать. По шагам, начиная с самого простого.</p>
<!-- /wp:paragraph -->

<!-- wp:code -->
<pre class="wp-block-code"><code>команда --исправление</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Проверка</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Как убедиться, что помогло:</p>
<!-- /wp:paragraph -->

<!-- wp:code -->
<pre class="wp-block-code"><code>команда --проверка
# ожидаемый вывод</code></pre>
<!-- /wp:code -->

<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-orange"} -->
<p class="badge-orange">Совет</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Как не наступить на это снова: настройка, проверка, автоматизация.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
