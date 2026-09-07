# Pattern: form email templates and their placeholders

Per-form letters live on `Form`: `{admin,user}_mail_subject` +
`{admin,user}_mail_body_md` (markdown, edited in `FormForm`). Rendering —
`App\Services\Forms\FormEmailTemplateRenderer`, sending —
`App\Jobs\SendFormSubmissionEmails`.

## Placeholders

Syntax `{{ key }}` / `{{ key.sub }}`, resolved by `Arr::get()` over the context
built in `buildContext()`:

| Placeholder                | Value                                                    |
| -------------------------- | -------------------------------------------------------- |
| `{{ field.<name> }}`       | submitted value of the field with that `name`             |
| `{{ files }}`              | every uploaded file as markdown `- [original name](url)`  |
| `{{ form.id }}`, `.name`   | the form                                                  |
| `{{ submission.id }}`, `.created_at`, `.status` | the submission                       |

- **File fields**: `data` holds no file entries (`SubmitFormAction` keeps `data`
  and `uploads` apart), so `field.<name>` is filled from the `files` relation
  grouped by `field_name` and yields **URLs only**, several joined by `\n`.
  Names come only from `{{ files }}`.
- **Unknown key → empty string**, silently (`:70`). A typo in a placeholder
  costs a blank spot in the letter, not an error.
- HTML body escapes values (`e()`), subject and text part do not — entities
  would reach the reader literally there.
- The body goes through `Str::markdown()` = GFM, whose `AutolinkExtension`
  turns a bare URL into a link. That is why `{{ field.cv }}` is clickable
  without any markdown around it.
- URLs are `Storage::disk($disk)->url($path)`, disk `public` by default
  (`SubmissionFilesStorer:39`) — a public link, no auth.

## Fallback without a template

`SendFormSubmissionEmails` uses the templated mail only when **both** subject
and body are non-empty; otherwise it sends `AdminFormSubmissionMail` /
`UserFormSubmissionMail` with the blades in `resources/views/emails/`. The
admin blade lists **file names without links** — a form with an empty template
loses the file URLs.

## Hint in the panel

`FormForm::placeholdersHint()` renders the list under both `MarkdownEditor`s
and appends the form's own enabled field names (their slugs are invisible
elsewhere in the panel). It returns `HtmlString`, so `<code>` in
`panel.email_placeholders_help` renders as markup — keep values escaped there.
Attachments for the user letter are a separate thing: static files from
`user_mail_attachments`, not submission uploads.
