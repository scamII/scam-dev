<?php
/**
 * Title: Заметка с цитатой
 * Slug: scam-dev/quote-note
 * Categories: text
 * Description: Короткая заметка с выделенной цитатой или фрагментом документации.
 * Keywords: заметка, цитата, документация
 * Post Types: post
 * Viewport Width: 800
 */
?>
<!-- wp:paragraph {"className":"text-lg"} -->
<p class="text-lg">Повод для заметки: что натолкнуло на мысль.</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><!-- wp:paragraph -->
<p>Цитата из документации, статьи или переписки. Достаточно короткая, чтобы её прочитали.</p>
<!-- /wp:paragraph --><cite>Источник, год</cite></blockquote>
<!-- /wp:quote -->

<!-- wp:paragraph -->
<p>Что это значит на практике. Разбор цитаты своими словами.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Первый вывод</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Второй вывод</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-orange"} -->
<p class="badge-orange">Совет</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Что с этим делать на практике — один конкретный шаг.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
