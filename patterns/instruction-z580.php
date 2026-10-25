<?php
/**
 * Title: Инструкция: настройка ноутбука Z580
 * Slug: scam-dev/instruction-z580
 * Categories: posts, text
 * Description: Полная инструкция по доводке Lenovo IdeaPad Z580 на Debian 13: шрифты, безопасность, стабильность, батарея.
 * Keywords: инструкция, debian, ноутбук, настройка, шрифты
 * Post Types: post
 * Viewport Width: 800
 */
?>
<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-coral"} -->
<p class="badge-coral">Как пользоваться</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Блоки идут по порядку, каждый можно делать отдельно. Перед первым шагом обязательно сделайте бэкап. После каждого блока есть проверка и откат. Команды с sudo спросят пароль.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- wp:heading -->
<h2 class="wp-block-heading" id="s0">0. Бэкап</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Пять минут, обязательно. Переменная <code>$BK</code> живёт только в текущем терминале — если откроете новый, задайте её заново.</p>
<!-- /wp:paragraph -->
<!-- wp:code -->
<pre class="wp-block-code"><code>BK="/home/scam/work/debian 13/setup/backup-$(date +%F_%H%M%S)"
mkdir -p "$BK" &amp;&amp; echo "Бэкап в: $BK"

sudo cp -a /etc "$BK/"                             # создаст $BK/etc
cp -a ~/.zshrc ~/.bashrc "$BK/" 2&gt;/dev/null
cp -a ~/.config "$BK/" 2&gt;/dev/null
apt-mark showmanual   &gt; "$BK/manual-packages.txt"
dpkg --get-selections &gt; "$BK/dpkg-selections.txt"
cp /etc/fstab "$BK/fstab.orig"

test -d "$BK/etc" &amp;&amp; echo "OK: $BK/etc на месте"
ls -la "$BK"</code></pre>
<!-- /wp:code -->
<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-coral"} -->
<p class="badge-coral">Кавычки обязательны</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>В пути есть пробел (debian 13). Без кавычек bash обрывает значение на пробеле, BK остаётся пустым, и вы получите 13/setup/backup-…: Нет такого файла или каталога.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-orange"} -->
<p class="badge-orange">Почему "$BK/", а не "$BK/etc"</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>При повторном запуске cp -a /etc "$BK/etc" кладёт /etc внутрь уже существующего $BK/etc — получается $BK/etc/etc. Секунды в метке времени дают новый каталог на каждый запуск.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- wp:heading -->
<h2 class="wp-block-heading" id="s1">1. Шрифты</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>В системе нет Inter, JetBrains Mono и Noto — только DejaVu и Cantarell, всего 65 семейств.</p>
<!-- /wp:paragraph -->
<!-- wp:code -->
<pre class="wp-block-code"><code>sudo apt update
sudo apt install fonts-inter fonts-jetbrains-mono fonts-noto-core fonts-noto-color-emoji
sudo fc-cache -f -v &gt;/dev/null

fc-match 'Inter'; fc-match 'JetBrains Mono'; fc-match 'Noto Sans'</code></pre>
<!-- /wp:code -->
<!-- wp:paragraph -->
<p>Все три команды <code>fc-match</code> должны вернуть сами эти шрифты, а не <code>DejaVuSans.ttf</code>.</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2 class="wp-block-heading" id="s2">2. Размер интерфейса безопасно</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Экран 1366×768 при 15.6″ — это ~100 DPI. Дефолтные 11 pt на такой высоте выглядят крупно.</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h3 class="wp-block-heading" id="s21">2.1 Масштаб и шрифты</h3>
<!-- /wp:heading -->
<!-- wp:code -->
<pre class="wp-block-code"><code>gsettings set org.gnome.desktop.interface text-scaling-factor 0.90
gsettings set org.gnome.desktop.interface font-name 'Cantarell 10'
gsettings set org.gnome.desktop.interface document-font-name 'Cantarell 10'
gsettings set org.gnome.desktop.wm.preferences titlebar-font 'Cantarell Bold 10'
gsettings set org.gnome.desktop.interface cursor-size 20</code></pre>
<!-- /wp:code -->
<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-orange"} -->
<p class="badge-orange">Не переусердствуйте</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>При 10 pt и масштабе 0.90 эффективный кегль выходит 9 pt = 12 px. У Cantarell самый низкий x-height из всех (0.482), поэтому высота строчной — всего 5.8 px: буквы слипаются и кажутся жёсткими. Если так — верните 1.0 и Cantarell 11.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- wp:heading -->
<h3 class="wp-block-heading" id="s22">2.2 Шрифт терминала</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Терминал берёт шрифт из профиля, а не из системы, если <code>use-system-font=false</code>.</p>
<!-- /wp:paragraph -->
<!-- wp:code -->
<pre class="wp-block-code"><code>P=$(gsettings get org.gnome.Terminal.ProfilesList default | tr -d "'")
gsettings set "org.gnome.Terminal.Legacy.Profile:/org/gnome/terminal/legacy/profiles:/:$P/" font 'JetBrains Mono 11'
gsettings set "org.gnome.Terminal.Legacy.Profile:/org/gnome/terminal/legacy/profiles:/:$P/" use-system-font false</code></pre>
<!-- /wp:code -->
<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-coral"} -->
<p class="badge-coral">Про моноширинные шрифты</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>У JetBrains Mono самый крупный x-height (0.550) — при 10 pt влезает 43 строки. У Terminus (0.458) — 55 строк, у Spleen 8×16 (0.438) — 57. Если текст кажется огромным, ставьте Terminus 11.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- wp:heading -->
<h3 class="wp-block-heading" id="s23">2.3 Компактные заголовки окон</h3>
<!-- /wp:heading -->
<!-- wp:code -->
<pre class="wp-block-code"><code>mkdir -p ~/.config/gtk-3.0
cat &gt; ~/.config/gtk-3.0/gtk.css &lt;&lt;'EOF'
headerbar,
.titlebar {
  min-height: 28px;
  padding: 0 4px;
}

headerbar button:not(.titlebutton) {
  min-height: 22px;
  min-width: 22px;
  padding: 0 6px;
}
EOF</code></pre>
<!-- /wp:code -->
<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-coral"} -->
<p class="badge-coral">Не делайте общее правило для кнопок окна</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Тема Orchis рисует кнопки закрытия кругом (border-radius: 9999px, 16×16, padding: 0). Правило headerbar button { padding: 0 6px } добавляет 6 px слева и справа — получается 34×22 при радиусе 9999px, то есть эллипс. Селектор :not(.titlebutton) это исключает.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-orange"} -->
<p class="badge-orange">Файл для GTK4 не нужен</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>libadwaita принудительно ставит тему Adwaita-empty и игнорирует вашу. ~/.config/gtk-4.0/gtk.css в таких приложениях только ломает вёрстку — удалите его. Темами управляют только GTK3-приложения: gnome-terminal, evince. Nautilus, Настройки, Text Editor — GTK4/libadwaita, они рисуются сами.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- wp:heading -->
<h3 class="wp-block-heading" id="s24">2.4 Альтернатива без CSS</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>В системе есть готовая компактная тема:</p>
<!-- /wp:paragraph -->
<!-- wp:code -->
<pre class="wp-block-code"><code>gsettings set org.gnome.desktop.interface gtk-theme 'Orchis-Dark-Compact'</code></pre>
<!-- /wp:code -->
<!-- wp:heading -->
<h2 class="wp-block-heading" id="s3">3. Безопасность средний риск</h2>
<!-- /wp:heading -->
<!-- wp:heading -->
<h3 class="wp-block-heading" id="s31">3.1 Что слушает сеть</h3>
<!-- /wp:heading -->
<!-- wp:code -->
<pre class="wp-block-code"><code>sudo ss -tlnp</code></pre>
<!-- /wp:code -->
<!-- wp:table {"className":"is-style-stripes"} -->
<figure class="wp-block-table is-style-stripes"><table><thead><tr><th>порт</th><th>кто</th><th>решение</th></tr></thead><tbody><tr><td><code>22</code></td><td>sshd</td><td>оставляем</td></tr><tr><td><code>3389</code></td><td>gnome-remote-desktop</td><td>оставляем</td></tr><tr><td><code>3080</code></td><td>dsh web</td><td>оставляем</td></tr><tr><td><code>631</code></td><td>cupsd</td><td>только localhost</td></tr></tbody></table></figure>
<!-- /wp:table -->
<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-coral"} -->
<p class="badge-coral">RDP слушает не только 3389</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>При negotiate-port=true сервер занимает первый свободный порт из диапазона 3389–3398. Файрвол, открывающий один порт, оборвёт подключение, если сервер ушёл на 3390.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-orange"} -->
<p class="badge-orange">RDP сейчас только на просмотр</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>view-only = true: клиент видит экран, но не управляет мышью и клавиатурой. Нужно управление — gsettings set org.gnome.desktop.remote-desktop.rdp view-only false и переподключиться.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- wp:heading -->
<h3 class="wp-block-heading" id="s32">3.2 Файрвол</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Входящие запрещены всем, кроме своей подсети <code>192.168.1.0/24</code>.</p>
<!-- /wp:paragraph -->
<!-- wp:code -->
<pre class="wp-block-code"><code>sudo apt install ufw
sudo ufw default deny incoming
sudo ufw default allow outgoing

sudo ufw allow from 192.168.1.0/24 to any port 22 proto tcp comment 'ssh'
sudo ufw allow from 192.168.1.0/24 to any port 3080 proto tcp comment 'dsh'
sudo ufw allow from 192.168.1.0/24 to any port 3389:3398 proto tcp comment 'RDP'

sudo ufw enable
sudo ufw status verbose</code></pre>
<!-- /wp:code -->
<!-- wp:heading -->
<h3 class="wp-block-heading" id="s33">3.3 Автообновления безопасности</h3>
<!-- /wp:heading -->
<!-- wp:code -->
<pre class="wp-block-code"><code>sudo apt install unattended-upgrades needrestart
sudo dpkg-reconfigure -plow unattended-upgrades    # ответ «Yes»
cat /etc/apt/apt.conf.d/20auto-upgrades</code></pre>
<!-- /wp:code -->
<!-- wp:paragraph -->
<p>Ожидаемое содержимое:</p>
<!-- /wp:paragraph -->
<!-- wp:code -->
<pre class="wp-block-code"><code>APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Unattended-Upgrade "1";</code></pre>
<!-- /wp:code -->
<!-- wp:heading -->
<h3 class="wp-block-heading" id="s34">3.4 API-ключ в ~/.zshrc</h3>
<!-- /wp:heading -->
<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-coral"} -->
<p class="badge-coral">Утечка</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Строка 41 содержит export PLUSVIBE_API_KEY="sk-pv-…" открытым текстом. При этом ~/.dsh/settings.yaml ждёт переменную VIBEAPI_API_KEY, а рабочий ключ лежит в ~/.dsh/.credentials.yaml. Строка в .zshrc — забытый мусор.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- wp:code -->
<pre class="wp-block-code"><code>sed -i '/PLUSVIBE_API_KEY/d' ~/.zshrc
chmod 600 ~/.dsh/.env ~/.dsh/.credentials.yaml</code></pre>
<!-- /wp:code -->
<!-- wp:heading -->
<h2 class="wp-block-heading" id="s4">4. Стабильность и скорость средний риск</h2>
<!-- /wp:heading -->
<!-- wp:heading -->
<h3 class="wp-block-heading" id="s41">4.1 Починить .zshrc</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Строка 40 склеена в одну: <code>eval "$(starship init zsh)"alias perf="…"</code>.
			Из-за этого <code>starship init</code> получает лишний аргумент, а алиас не создаётся.</p>
<!-- /wp:paragraph -->
<!-- wp:code -->
<pre class="wp-block-code"><code>python3 - &lt;&lt;'EOF'
p = "/home/scam/.zshrc"
s = open(p, encoding="utf-8").read()
bad  = 'eval "$(starship init zsh)"alias perf="powerprofilesctl set performance"'
good = 'eval "$(starship init zsh)"\nalias perf="powerprofilesctl set performance"'
assert bad in s, "строка не найдена — правим руками"
open(p, "w", encoding="utf-8").write(s.replace(bad, good))
print("ok")
EOF

zsh -ic 'alias perf'</code></pre>
<!-- /wp:code -->
<!-- wp:heading -->
<h3 class="wp-block-heading" id="s42">4.2 /etc/fstab: noatime и NAS без зависаний</h3>
<!-- /wp:heading -->
<!-- wp:code -->
<pre class="wp-block-code"><code>sudo cp /etc/fstab /etc/fstab.bak
sudo nano /etc/fstab
sudo systemctl daemon-reload
sudo mount -a
mount | grep -E 'nas|on / |on /home '</code></pre>
<!-- /wp:code -->
<!-- wp:paragraph -->
<p>Для корня и <code>/home</code> добавьте <code>noatime</code>. Для NAS — <code>nofail</code> и
			<code>x-systemd.automount</code>, иначе при выключенном NAS вход в систему тормозит.</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h3 class="wp-block-heading" id="s43">4.3 zram</h3>
<!-- /wp:heading -->
<!-- wp:code -->
<pre class="wp-block-code"><code>sudo apt install systemd-zram-generator
sudo tee /etc/systemd/zram-generator.conf &gt;/dev/null &lt;&lt;'EOF'
[zram0]
zram-size = min(ram / 2, 8192)
compression-algorithm = zstd
EOF
sudo systemctl daemon-reload
sudo systemctl start systemd-zram-setup@zram0.service
zramctl
swapon --show</code></pre>
<!-- /wp:code -->
<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-orange"} -->
<p class="badge-orange">Не удаляйте /dev/sda3</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Диск swap нужен для гибернации. С zram они сосуществуют: zram получает приоритет.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- wp:heading -->
<h3 class="wp-block-heading" id="s44">4.4 irqbalance</h3>
<!-- /wp:heading -->
<!-- wp:code -->
<pre class="wp-block-code"><code>sudo apt install irqbalance
sudo systemctl enable --now irqbalance
systemctl status irqbalance --no-pager</code></pre>
<!-- /wp:code -->
<!-- wp:heading -->
<h3 class="wp-block-heading" id="s45">4.5 Гибернация</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Сейчас <code>/sys/power/disk</code> показывает <code>[disabled]</code>, хотя ядро собрано с
			<code>CONFIG_HIBERNATION=y</code> и <code>RESUME=</code> прописан.</p>
<!-- /wp:paragraph -->
<!-- wp:code -->
<pre class="wp-block-code"><code>cat /sys/power/disk
cat /proc/swaps
sudo dmesg | grep -iE 'hibernat|swap|resume'
sudo update-initramfs -u -k all</code></pre>
<!-- /wp:code -->
<!-- wp:paragraph -->
<p>Если после перезагрузки всё ещё <code>[disabled]</code>:</p>
<!-- /wp:paragraph -->
<!-- wp:code -->
<pre class="wp-block-code"><code>sudo sed -i 's|^GRUB_CMDLINE_LINUX_DEFAULT=.*|GRUB_CMDLINE_LINUX_DEFAULT="quiet splash resume=UUID=7ff9b6b8-8e96-48e1-a2a5-9bfc568a7906"|' /etc/default/grub
sudo update-grub
sudo reboot</code></pre>
<!-- /wp:code -->
<!-- wp:heading -->
<h3 class="wp-block-heading" id="s46">4.6 Репозитории</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Сейчас только <code>stable main contrib</code> — без <code>non-free-firmware</code> и backports.</p>
<!-- /wp:paragraph -->
<!-- wp:code -->
<pre class="wp-block-code"><code>sudo cp /etc/apt/sources.list /etc/apt/sources.list.bak
sudo tee /etc/apt/sources.list &gt;/dev/null &lt;&lt;'EOF'
deb http://mirror.cloudhosting.lv/debian/ trixie main contrib non-free non-free-firmware
deb http://mirror.cloudhosting.lv/debian/ trixie-updates main contrib non-free non-free-firmware
deb http://security.debian.org/debian-security trixie-security main contrib non-free non-free-firmware
deb http://mirror.cloudhosting.lv/debian/ trixie-backports main contrib non-free non-free-firmware
EOF
sudo apt update &amp;&amp; sudo apt upgrade</code></pre>
<!-- /wp:code -->
<!-- wp:heading -->
<h2 class="wp-block-heading" id="s5">5. Батарея и тепло низкий риск</h2>
<!-- /wp:heading -->
<!-- wp:heading -->
<h3 class="wp-block-heading" id="s51">5.1 thermald</h3>
<!-- /wp:heading -->
<!-- wp:code -->
<pre class="wp-block-code"><code>sudo apt install thermald
sudo systemctl enable --now thermald
systemctl status thermald --no-pager</code></pre>
<!-- /wp:code -->
<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-coral"} -->
<p class="badge-coral">TLP и power-profiles-daemon несовместимы</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Они борются за одни настройки. Для этой машины достаточно PPD + thermald: PPD уже интегрирован в GNOME, а лимит заряда делает lenovo-conservation-mode.service.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- wp:heading -->
<h3 class="wp-block-heading" id="s52">5.2 Мониторинг</h3>
<!-- /wp:heading -->
<!-- wp:code -->
<pre class="wp-block-code"><code>sudo apt install lm-sensors smartmontools
sudo sensors-detect --auto
sensors
sudo smartctl -a /dev/sda
sudo smartctl -d sat -a /dev/sdb    # HDD в USB-кармане — только с -d sat</code></pre>
<!-- /wp:code -->
<!-- wp:heading -->
<h3 class="wp-block-heading" id="s53">5.3 Консервация батареи</h3>
<!-- /wp:heading -->
<!-- wp:code -->
<pre class="wp-block-code"><code>cat /sys/bus/platform/drivers/ideapad_acpi/*/conservation_mode   # 1 = до 60 %

# перед поездкой — полный заряд:
ls /sys/bus/platform/drivers/ideapad_acpi/                       # узнать имя
echo 0 | sudo tee /sys/bus/platform/drivers/ideapad_acpi/VPC2004:00/conservation_mode</code></pre>
<!-- /wp:code -->
<!-- wp:heading -->
<h2 class="wp-block-heading" id="s6">6. Финал</h2>
<!-- /wp:heading -->
<!-- wp:code -->
<pre class="wp-block-code"><code>sudo apt full-upgrade
sudo reboot</code></pre>
<!-- /wp:code -->
<!-- wp:code -->
<pre class="wp-block-code"><code>ufw status | head -3
systemctl --failed
zramctl
systemctl status thermald irqbalance --no-pager | grep -E 'Active|●'
cat /sys/power/disk
sensors | head</code></pre>
<!-- /wp:code -->
<!-- wp:heading -->
<h2 class="wp-block-heading" id="check">Чек-лист</h2>
<!-- /wp:heading -->
<!-- wp:heading -->
<h2 class="wp-block-heading" id="check">Чек-лист</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>☐ Бэкап в <code>setup/backup-…</code></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>☐ Шрифты Inter / JetBrains Mono / Noto установлены</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>☐ Масштаб интерфейса выставлен и читается</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>☐ Кнопки окна круглые, заголовки компактные</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>☐ ufw: 22, 3080, 3389–3398 только для 192.168.1.0/24</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>☐ unattended-upgrades включён</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>☐ Ключ из <code>.zshrc</code> убран</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>☐ <code>.zshrc</code> починен, <code>alias perf</code> работает</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>☐ fstab: noatime + NAS через automount</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>☐ zram ~7.7 ГБ + sysctl</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>☐ irqbalance работает</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>☐ Гибернация включена (или осознанно оставлена выключенной)</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>☐ Репозитории: non-free-firmware + backports</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>☐ thermald работает</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>☐ sensors и smartctl показывают данные</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>☐ <code>systemctl --failed</code> пуст</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->
<!-- wp:heading -->
<h2 class="wp-block-heading" id="dont">Чего не делать</h2>
<!-- /wp:heading -->
<!-- wp:table {"className":"is-style-stripes"} -->
<figure class="wp-block-table is-style-stripes"><table><thead><tr><th>что</th><th>почему</th></tr></thead><tbody><tr><td>TLP + power-profiles-daemon</td><td>Конфликт, борются за одни настройки</td></tr><tr><td><code>discard</code> в fstab для SSD</td><td><code>fstrim.timer</code> уже включён</td></tr><tr><td>Ядро из backports</td><td>На Ivy Bridge штатное 6.12 работает, выигрыша нет</td></tr><tr><td><code>preload</code></td><td>На SSD при 15 ГБ RAM бесполезен</td></tr><tr><td><code>powertop --auto-tune</code> вслепую</td><td>Отключает нужное, например USB-свисток Wi-Fi</td></tr></tbody></table></figure>
<!-- /wp:table -->
<!-- wp:heading -->
<h2 class="wp-block-heading" id="out">За рамками</h2>
<!-- /wp:heading -->
<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-coral"} -->
<p class="badge-coral">Железо</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Внутренний Wi-Fi не определяется вообще — lspci видит только Ethernet. Работает через USB-свисток RTL88x2BU. Похоже на отсутствующую карту Mini-PCIe или whitelist в BIOS 2012 года. Программно не лечится.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- wp:group {"className":"glass p-6","layout":{"type":"constrained"}} -->
<div class="wp-block-group glass p-6"><!-- wp:paragraph {"className":"badge-coral"} -->
<p class="badge-coral">Прошивка</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>BIOS от 25.12.2012, обновлений через fwupd для этой модели нет.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
