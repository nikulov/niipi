# Recipe: auditing `{{ ... }}` placeholders in form mail templates

Use when someone reports "the letter arrived with an empty line" or asks to
check the forms. Read-only until the fix step.

The templates live in data, not in code — `forms.admin_mail_subject`,
`admin_mail_body_md`, `user_mail_subject`, `user_mail_body_md` — so tests and
grep over the repo see nothing. Only the prod DB answers.

## Why it is silent

`FormEmailTemplateRenderer::replacePlaceholders()` resolves every
`{{ key }}` through `Arr::get($context, $key)` and returns `''` for a miss —
no exception, no log. The letter goes out with `Сообщение:` and nothing after
it, the submission is marked `Sent`, and nobody notices until a client
complains.

Valid keys (`buildContext()`): `form.id`, `form.name`, `submission.id`,
`submission.created_at`, `submission.status`, `files`, and `field.<name>` for
each field of that form. A bare container — `field`, `form`, `submission` —
is an array, which the renderer also flattens to `''`.

## The audit

Dump both tables and match locally; do not analyse over ssh line by line.

```sh
ssh <prod> 'cd /var/www/niipigrad-prod/current && php artisan tinker --execute="
  echo json_encode([
    \"forms\"  => DB::table(\"forms\")->orderBy(\"id\")->get(),
    \"fields\" => DB::table(\"form_fields\")->orderBy(\"form_id\")->get(),
  ], JSON_UNESCAPED_UNICODE);" < /dev/null' > forms-prod.json
```

Then extract with the renderer's own regex — `/\{\{\s*([a-zA-Z0-9_.-]+)\s*\}\}/`
— and classify each key against the list above. Keep the dump: it is the
rollback copy for the fix step.

**A disabled field is as broken as a missing one.** `PublicForm` loads only
`is_enabled = true`, so `field.<name>` of a disabled field never has a value.
Report it separately from a genuine typo — the fix may be to enable the field
rather than to edit the template.

## Fixing

`str_replace` on the read value, write back through a bound parameter — not
`DB::raw("REPLACE(...)")`, whose string literals need double quotes that
MySQL only accepts outside `ANSI_QUOTES`. `Form` has no observer and no cache,
and `SendFormSubmissionEmails` reloads the row per job, so an `UPDATE` takes
effect on the next submission.

## Audit of 2026-09-07 (13 forms, 100 fields)

Fixed in prod DB: `{{ filed.message }}` → `{{ field.message }}` (#1001
«Обратная связь», the message body was missing from every admin letter) and
`{{ filed.files }}` → `{{ files }}` (#1013 «СТО_ФЛ»).

Left as they are, deliberately:

- `{{ field.inn }}` in the admin template of #1000, #1002, #1006, #1010,
  #1012, #1015 — `inn` exists but is disabled in all six. The user chose to
  keep the line.
- `{{ field.changes }}` in #1013 «СТО_ФЛ» — no such field on that form (only
  the ЮЛ variants have it), so `Изменения:` is always empty. Not a typo,
  needs a decision.
