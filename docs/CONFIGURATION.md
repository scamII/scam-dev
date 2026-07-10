# Конфигурация интеграций

Все секреты задаются вне Git: в `wp-config.php`, secret manager или container environment.

## Matrix

Обязательные значения:

```php
define( 'MATRIX_HOMESERVER_URL', 'https://matrix.example.com' );
define( 'MATRIX_EXPECTED_HOST', 'matrix.example.com' );
define( 'MATRIX_ADMIN_TOKEN', '...' );
define( 'MATRIX_CHAT_URL', 'https://chat.example.com' );
```

Публичная регистрация закрыта, пока явно не задано:

```php
define( 'SCAM_DEV_MATRIX_REGISTRATION_ENABLED', true );
```

Кроме того, нужен invite code или Turnstile. Для Turnstile задаются:

```php
define( 'SCAM_DEV_TURNSTILE_SITE_KEY', '...' );
define( 'SCAM_DEV_TURNSTILE_SECRET_KEY', '...' );
define( 'SCAM_DEV_TURNSTILE_EXPECTED_HOST', 'example.com' );
```

`SCAM_DEV_TURNSTILE_EXPECTED_HOST` должен совпадать с hostname публичной страницы регистрации. Если константа не задана, используется hostname WordPress-сайта.

Дополнительные limits:

```php
define( 'SCAM_DEV_MATRIX_ATTEMPT_LIMIT', 5 );
define( 'SCAM_DEV_MATRIX_IP_DAILY_LIMIT', 2 );
define( 'SCAM_DEV_MATRIX_GLOBAL_DAILY_LIMIT', 20 );
```

Reverse proxy обязан передавать реальный client IP на уровне web server. Плагин намеренно не доверяет пользовательским `X-Forwarded-For` headers.

## VK

```php
define( 'VK_API_TOKEN', '...' );
```

Importer:

- отправляет token в POST body;
- обрабатывает максимум 5 записей в cron batch;
- ограничивает image 5 МБ;
- принимает только HTTPS VK media hosts;
- сначала создаёт draft;
- откатывает post/media при финальной ошибке.

## Theme updater

```php
define( 'SCAM_DEV_UPDATE_PUBLIC_KEY', '<base64 Ed25519 public key>' );
```

Можно переопределить manifest URL:

```php
define(
    'SCAM_DEV_UPDATE_MANIFEST_URL',
    'https://scam-dev.ru/theme-update.json'
);
```

Package host обязан совпадать с host manifest URL.
