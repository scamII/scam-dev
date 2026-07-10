# Scam Dev

Репозиторий содержит тему WordPress **Scam Dev** и шесть отдельных функциональных
плагинов. Тема отвечает за отображение; интеграции и данные вынесены в плагины.

## Компоненты

- `scam-dev` — гибридная тема WordPress, Tailwind CSS и React-улучшения.
- `scam-dev-donate-widget` — локальный QR и блок поддержки.
- `scam-dev-gallery` — CPT галерей, shortcode и Gutenberg-блок.
- `scam-dev-matrix` — защищённая Matrix-регистрация и кешируемый status widget.
- `scam-dev-seo` — Open Graph, Twitter Card и JSON-LD при отсутствии другого SEO-плагина.
- `scam-dev-svg` — fail-closed SVG sanitizer.
- `scam-dev-vk-import` — фоновый пакетный импорт из VK.

## Требования

- WordPress 6.5 или новее.
- PHP 8.0 или новее с DOM, fileinfo и sodium.
- Node.js 22 для сборки.
- Composer для quality checks.

## Локальный запуск

```bash
cp .env.example .env
# Замените оба DB-пароля случайными значениями.
npm ci
make dev
```

WordPress будет доступен только на `http://127.0.0.1:8080`.

phpMyAdmin не запускается по умолчанию:

```bash
docker compose --profile debug up -d phpmyadmin
```

Он публикуется только на `127.0.0.1:8081` и не выполняет автоматический root login.

Docker монтирует в webroot чистую копию темы из `.dev/theme/scam-dev`, а не весь Git-репозиторий.

## Сборка и проверки

```bash
npm ci
composer install
make quality
make package
```

`make quality` запускает:

- JavaScript lint;
- CSS lint для новых/изменённых styles;
- static regression checks;
- WPCS/PHPCompatibility;
- PHP syntax.

CI дополнительно выполняет runtime npm audit, Composer audit, production build и smoke test ZIP-архива.

## JavaScript source

Production bundle собирается из:

```text
assets/js/src/index.jsx
assets/js/components/
```

Generated `assets/js/app.js` не хранится в Git. Он появляется только в build/release/dev staging.

## Matrix

Публичная регистрация **закрыта по умолчанию**. Для её включения необходимы:

```php
define( 'MATRIX_HOMESERVER_URL', 'https://matrix.example.com' );
define( 'MATRIX_EXPECTED_HOST', 'matrix.example.com' );
define( 'MATRIX_ADMIN_TOKEN', '...' );
define( 'SCAM_DEV_MATRIX_REGISTRATION_ENABLED', true );
```

И минимум один anti-abuse механизм:

```php
define( 'SCAM_DEV_MATRIX_INVITE_CODE', 'long-random-invite-code' );
```

или Cloudflare Turnstile:

```php
define( 'SCAM_DEV_TURNSTILE_SITE_KEY', '...' );
define( 'SCAM_DEV_TURNSTILE_SECRET_KEY', '...' );
```

Для публичного production рекомендуется одновременно invite/approval и Turnstile.

## Signed theme updates

Updater принимает только Ed25519-signed manifest. Публичный ключ задаётся вне репозитория:

```php
define( 'SCAM_DEV_UPDATE_PUBLIC_KEY', '<base64-public-key>' );
```

Приватный ключ хранится только в секретном хранилище deployment environment. Без публичного ключа custom update channel безопасно отключён.

Подробнее: [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md).

## Структура

```text
assets/                 runtime assets и frontend source
inc/                    presentation-модули темы
plugins/                независимые функциональные плагины
scripts/                static checks, packaging, signing, deployment
template-parts/         шаблоны темы
docs/                   эксплуатационная документация
.github/workflows/      CI/CD
```

## Безопасность

Не публикуйте уязвимости в обычных issues. Используйте GitHub Security Advisory, как описано в [`SECURITY.md`](SECURITY.md).
