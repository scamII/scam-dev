<?php
/**
 * Title: Конфиг целиком
 * Slug: scam-dev/full-config
 * Categories: posts, text
 * Description: Готовый файл конфигурации с построчным разбором параметров.
 * Keywords: конфиг, настройка, файл, пример
 * Post Types: post
 * Viewport Width: 800
 */
?>
<!-- wp:paragraph {"className":"text-lg"} -->
<p class="text-lg">Для чего этот конфиг и куда его класть. Сразу укажите путь к файлу.</p>
<!-- /wp:paragraph -->

<!-- wp:code -->
<pre class="wp-block-code"><code>[section]
option = value
another = value

[second]
enabled = true
path = /var/lib/example</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Что тут важно</h2>
<!-- /wp:heading -->

<!-- wp:table {"className":"is-style-stripes"} -->
<figure class="wp-block-table is-style-stripes"><table><thead><tr><th>Параметр</th><th>Значение</th><th>Зачем</th></tr></thead><tbody><tr><td><code>option</code></td><td><code>value</code></td><td>Что делает</td></tr><tr><td><code>another</code></td><td><code>value</code></td><td>Что делает</td></tr><tr><td><code>enabled</code></td><td><code>true</code></td><td>Что делает</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Применить и проверить</h2>
<!-- /wp:heading -->

<!-- wp:code -->
<pre class="wp-block-code"><code>sudo systemctl restart пример
sudo systemctl status пример --no-pager
journalctl -u пример -n 20 --no-pager</code></pre>
<!-- /wp:code -->

<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-coral"} -->
<p class="badge-coral">Важно</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Что сломается, если ошибиться в конкретном параметре. И как откатить: где лежит резервная копия.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
