# Безопасный deployment

## Модель

Deployment использует immutable release directories и атомарное переключение
symlink. PHP-код принадлежит deployment user, а web server получает только read
access.

Не используйте `www-data` как владельца PHP-кода.

## Ed25519 update key

Сгенерируйте ключи в защищённой среде:

```bash
php -r '
$keypair = sodium_crypto_sign_keypair();
file_put_contents(
    "scam-dev-ed25519-private.key",
    base64_encode(sodium_crypto_sign_secretkey($keypair))
);
file_put_contents(
    "scam-dev-ed25519-public.key",
    base64_encode(sodium_crypto_sign_publickey($keypair))
);
'
chmod 600 scam-dev-ed25519-private.key
```

Приватный ключ:

- не коммитится;
- не копируется на web server без необходимости;
- хранится в secret manager или защищённом CI secret/file;
- указывается через `UPDATE_SIGNING_PRIVATE_KEY_FILE`.

Публичный ключ добавляется в `wp-config.php`:

```php
define(
    'SCAM_DEV_UPDATE_PUBLIC_KEY',
    '<содержимое scam-dev-ed25519-public.key>'
);
```

## Конфигурация

```bash
cp .env.example .env
chmod 600 .env
```

Заполните deployment variables и выполните:

```bash
make quality
make deploy
```

Процесс:

1. собирает production assets;
2. формирует theme/plugin ZIP;
3. проверяет структуру и PHP syntax;
4. создаёт SHA-256;
5. подписывает canonical update payload;
6. загружает archives во временный release;
7. проверяет PHP на сервере;
8. атомарно переключает symlinks;
9. публикует ZIP и signed manifest;
10. выполняет health check;
11. откатывает symlinks при ошибке.

## Filesystem

Рекомендуется:

```text
/var/www/releases/scam-dev/       deploy:www-data 0755
/var/www/html/wp-content/themes/  root:www-data   0755
/var/www/html/wp-content/plugins/ root:www-data   0755
/var/www/html/wp-content/uploads/ www-data:www-data writable
```

В production:

```php
define( 'DISALLOW_FILE_EDIT', true );
```

Если WordPress не выполняет updates самостоятельно:

```php
define( 'DISALLOW_FILE_MODS', true );
```

## Rollback

Previous symlink targets сохраняются в:

```text
/var/www/releases/scam-dev/state/
```

`deploy.sh` автоматически восстанавливает их, если `/wp-json/` не проходит health check.
