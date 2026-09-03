# Recipe: triaging an error report from prod

Use when someone says "something is broken in production" without a stack
trace. Read-only, safe to run in full. Server facts (paths, services, valkey,
`LOG_LEVEL`) are in [../workflow.md](../workflow.md#логи-и-сервисы-на-проде-проверено-2026-09-03).

`$P=/var/www/niipigrad-prod/current`, prod host is in the user's ssh config.

## 1. Application log: how much and of what

```sh
ls -la $P/storage/logs/
grep -oE "production\.(ERROR|WARNING|CRITICAL)" $P/storage/logs/laravel-<date>.log | sort | uniq -c
grep -n "^\[2026" $P/storage/logs/laravel-<date>.log | cut -c1-200
```

`LOG_LEVEL=error`, so warnings never appear and a quiet day leaves no file at
all. An empty `logs/` is not proof that nothing broke.

## 2. Separate bots from people — the log alone will not tell you

Every entry in step 1 needs its nginx line before it means anything: the ten
500s of 2026-08-28 all came from one `python-requests` scanner in two seconds.

```sh
grep "livewire/update" /var/log/nginx/access.log | awk '$9!=200' | sed -E 's/"Mozilla.*//'
(cat /var/log/nginx/access.log{,.1}; zcat /var/log/nginx/access.log.*.gz) \
  | grep -E "livewire/(update|upload-file)" | awk '$9!=200 {print $9}' | sort | uniq -c
```

A referer of `-` plus a scripted User-Agent is a probe. A referer of
`https://niipigrad.ru/<page>` with a browser UA is a person, and worth
chasing.

## 3. Follow one client end to end

```sh
grep "^<ip> " /var/log/nginx/access.log \
  | grep -vE "\.(css|js|woff2?|png|jpe?g|svg|webp|ico|map)" | cut -c1-200
```

This is what turns a status code into a story: 419 → reload 3 s later → no
further POST means the visitor lost their input and left (#44). Codes only
nginx sees — 419, 499 — never reach the application log.

## 4. Did the work actually happen

```sh
cd $P && php artisan tinker --execute='
  echo DB::table("form_submissions")->count().PHP_EOL;
  echo DB::table("failed_jobs")->count().PHP_EOL;'
tail -5 $P/storage/logs/worker.log
```

A submission row carries `status` and `error_message`; the mail job shows up
in `worker.log` as `RUNNING` → `DONE`.

## 5. Rule out a cached page before blaming the session

Two requests from clean cookie jars must return different `csrf-token` values
and fresh `Set-Cookie`. Same token twice = the HTML is cached somewhere and
every visitor inherits a dead token.

```sh
for i in 1 2; do
  curl -s -D - -o p$i.html https://niipigrad.ru/<page> | grep -iE "^(HTTP/|set-cookie|cache-control)"
  grep -oE '<meta name="csrf-token" content="[^"]{0,20}' p$i.html
done
```

## 6. Reproducing a Livewire attack locally

Take the snapshot out of the rendered page and replay it against the local
app — this is the only way to see what a client-supplied `updates` map does,
and it needs no valid checksum of your own (the snapshot stays untouched):

```
POST /livewire/update
{"_token": "<meta csrf>", "components": [{"snapshot": "<wire:snapshot, html-unescaped>",
                                          "updates": {"limit": 999999}, "calls": []}]}
```

Worked example and what each payload used to do —
[../plans/archived/bugs.md](../plans/archived/bugs.md) #9.
