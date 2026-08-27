# nginx на проде

Референс-копии боевых конфигов с `89.108.113.198`. Содержимое совпадает с
сервером один-в-один (проверено по md5 27.08.2026).

На сервере файлы лежат в `/etc/nginx/sites-available/` **без расширения**
(`niipigrad-prod`, не `niipigrad-prod.conf`), и все три — симлинки в
`sites-enabled/`, не только `zz-catch-all`. Суффикс `.conf` здесь нужен
только для подсветки синтаксиса в редакторе: копируя на сервер, имя брать
без него.

Правишь сервер — правь и здесь, иначе копия протухнет.

- [niipigrad-prod.conf](niipigrad-prod.conf) — canonical HTTPS +
  www-HTTPS-301 (с HSTS) + http-80-301 (без HSTS, по RFC 6797). HSTS и три
  security-header'а продублированы в `/vendor/livewire/`,
  `/vendor/filament/`, `/storage/` и статику — `add_header` **не
  наследуется** в location со своим `add_header`. TLS 1.2/1.3, HTTP/2,
  `= /index.php` как единственная точка exec, `$realpath_root` для
  OPcache-friendly деплоя, dotfiles-deny перед статикой. С 27.08.2026 —
  четыре `location =` с `return 410` на `/feed{,/}` и `/comments/feed{,/}`.
- [niipigrad-stage.conf](niipigrad-stage.conf) — HSTS `max-age=300`,
  Certbot-managed http-server не тронут. В vendor/статике HSTS не
  дублируется, и поднимать max-age не планируется: прод пинит stage через
  `includeSubDomains`, собственный max-age стенда ничего не решает.
  С 27.08.2026 exec приведён к проду (`= /index.php` + `$realpath_root`),
  dotfiles-deny перенесён вперёд статики — regex-порядок теперь как на проде.
- [zz-catch-all.conf](zz-catch-all.conf) — голый IP: 80 → 301 на домен,
  443 → `ssl_reject_handshake`. Префикс `zz-` обязателен: файл должен
  грузиться после `niipigrad-prod` из-за `ipv6only` в первом объявлении
  адреса.

Порядок правки на сервере: бэкап в `/root/backup/`, затем `nginx -t`,
только потом `systemctl reload nginx`.

Обоснования всех решений — в [../../plans/archived/nginx-hsts.md](../../plans/archived/nginx-hsts.md).
