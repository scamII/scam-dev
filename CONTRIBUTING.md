# Участие в разработке

## Рабочий процесс

1. Создайте ветку от актуального `main`.
2. Внесите небольшие тематические изменения.
3. Добавьте или обновите regression checks.
4. Выполните полный quality gate.
5. Создайте Pull Request в `main`.

```bash
npm ci
composer install
make quality
make package
```

## Требования к изменениям

- PHP: WordPress Coding Standards, PHP 8.0+.
- JavaScript: ESLint-конфигурация `@wordpress/scripts`.
- CSS: Stylelint для изменённых styles.
- State-changing WordPress actions: nonce и capability check.
- Все динамические данные экранируются в момент вывода.
- Внешние URL ограничиваются HTTPS и ожидаемыми hosts.
- Секреты не добавляются в repository, logs, screenshots и fixtures.
- Generated `assets/js/app.js` и `build/` не коммитятся.
- Плагины должны владеть собственными assets и не зависеть от handles темы.

## Conventional Commits

- `feat:` — новая функция;
- `fix:` — исправление;
- `security:` — security hardening;
- `refactor:` — изменение структуры;
- `test:` — проверки;
- `docs:` — документация;
- `chore:` — обслуживание.

## Сообщение об ошибке

Обычные ошибки можно создавать в GitHub Issues репозитория `scamII/scam-dev`.

Уязвимости отправляйте только через private GitHub Security Advisory.
