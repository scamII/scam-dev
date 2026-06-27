<?php

/**
 * Переиспользуемый блок: ненавязчивая плашка поддержки через Т-Банк.
 *
 * Используется в конце статей, на главной, в сайдбаре — везде, где нужно
 * аккуратно напомнить о возможности поддержать проект.
 */

if (! defined('ABSPATH')) {
    exit;
}

$donate_url   = 'https://tbank.ru/cf/2x90jZaUieT';
$heart_icon   = '<svg class="w-4 h-4 flex-shrink-0" style="color:var(--color-accent)" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>';
?>
<div class="donate-inline glass glass-hover" style="padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin:1.5rem 0;border-left:3px solid var(--color-accent)">
    <div style="display:flex;align-items:center;gap:0.625rem;min-width:0">
        <?php echo $heart_icon; ?>
        <p style="margin:0;font-size:0.85rem;line-height:1.4;color:var(--color-text-secondary)">
            <?php esc_html_e('Проект существует благодаря сообществу. Помогите серверу жить.', 'scam-dev'); ?>
        </p>
    </div>
    <a href="<?php echo esc_url($donate_url); ?>" target="_blank" rel="noopener"
        class="donate-btn"
        style="display:inline-flex;align-items:center;gap:6px;background:var(--color-accent);color:#19253b;font-weight:600;font-size:0.8rem;padding:0.5rem 1.25rem;border-radius:9999px;text-decoration:none;white-space:nowrap;transition:all 0.25s;flex-shrink:0"
        onmouseover="this.style.boxShadow='0 0 20px color-mix(in srgb, var(--color-accent) 60%, transparent)'"
        onmouseout="this.style.boxShadow='none'">
        ♥ <?php esc_html_e('Поддержать', 'scam-dev'); ?>
    </a>
</div>