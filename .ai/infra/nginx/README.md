# nginx на проде

Референс-копии боевых конфигов с `89.108.113.198`. Совпадают с сервером
один-в-один и заливаются как есть в `/etc/nginx/sites-enabled/`
(одноимённые файлы, `zz-catch-all.conf` → симлинк из `sites-available/`).
Правишь сервер — правь и здесь, иначе копия протухнет.

- [niipigrad-prod.conf](niipigrad-prod.conf) — canonical HTTPS +
  www-HTTPS-301 (с HSTS) + http-80-301 (без HSTS, по RFC 6797). HSTS и три
  security-header'а продублированы в `/vendor/livewire/`,
  `/vendor/filament/`, `/storage/` и статику — `add_header` **не
  наследуется** в location со своим `add_header`. TLS 1.2/1.3, HTTP/2,
  `= /index.php` как единственная точка exec, `$realpath_root` для
  OPcache-friendly деплоя, dotfiles-deny перед статикой.
- [niipigrad-stage.conf](niipigrad-stage.conf) — HSTS `max-age=300`,
  Certbot-managed http-server не тронут. В vendor/статике HSTS не
  дублируется осознанно; при подъёме max-age до боевого — добавить.
- [zz-catch-all.conf](zz-catch-all.conf) — голый IP: 80 → 301 на домен,
  443 → `ssl_reject_handshake`. Префикс `zz-` обязателен: файл должен
  грузиться после `niipigrad-prod` из-за `ipv6only` в первом объявлении
  адреса.

Порядок правки на сервере: бэкап в `/root/backup/`, затем `nginx -t`,
только потом `systemctl reload nginx`.

Обоснования всех решений — в [../../plans/archived/nginx-hsts.md](../../plans/archived/nginx-hsts.md).
