# Scam Dev

Современная гибридная тема WordPress с Tailwind CSS, React-компонентами и тёмной/светлой темой. Построена с использованием `wp-scripts` и webpack.

## Возможности

- **Tailwind CSS v4** — утилитарный CSS-фреймворк
- **Тёмная/светлая тема** — переключение с сохранением в `localStorage`
- **React-компоненты** — ProgressBar, TableOfContents, BurgerMenu, ThemeToggle, BackToTop, CopyCode, RelatedPosts
- **SEO** — Open Graph, Twitter Cards, Schema.org, breadcrumbs
- **Подсветка кода** — highlight.js с тёмной/светлой темой
- **RTL-поддержка** — скомпилированные RTL-стили
- **Docker-окружение** — WordPress + MySQL + PhpMyAdmin

## Требования

- WordPress ≥ 6.5
- PHP ≥ 8.0
- Node.js ≥ 20 (для разработки)

## Быстрый старт (Docker)

```bash
make dev
```

Открыть:
- WordPress: [http://localhost:8080](http://localhost:8080)
- PhpMyAdmin: [http://localhost:8081](http://localhost:8081)

## Разработка

```bash
npm ci                    # Установка зависимостей
npm run build             # Сборка CSS/JS
npm run start             # Dev-сервер с hot reload
```

## Структура

```
scam-dev/
├── assets/
│   ├── css/              # Скомпилированные стили
│   ├── js/               # JavaScript и React-компоненты
│   └── scss/             # Исходные SCSS/Tailwind
├── inc/                  # PHP-модули темы
├── template-parts/       # Переиспользуемые шаблоны
├── .gitverse/            # Конфигурация GitVerse CI/CD
├── docker-compose.yml    # Docker-окружение
├── Makefile              # Команды разработки
└── webpack.config.js     # Сборщик wp-scripts
```

## Сборка и CI/CD

CI/CD пайплайн описан в [`.gitverse/workflows/build.yaml`](.gitverse/workflows/build.yaml):

- Установка npm-зависимостей
- Сборка CSS/JS через `wp-scripts build`
- Упаковка темы в ZIP-архив
- Публикация артефакта

## Лицензия

GNU General Public License v2 или новее. См. [LICENSE](LICENSE).
