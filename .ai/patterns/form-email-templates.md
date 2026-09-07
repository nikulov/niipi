# Pattern: form email templates and their placeholders

Per-form letters live on `Form`: `{admin,user}_mail_subject` +
`{admin,user}_mail_body_md` (markdown, edited in `FormForm`). Rendering —
`App\Services\Forms\FormEmailTemplateRenderer`, sending —
`App\Jobs\SendFormSubmissionEmails`.

## Placeholders

Syntax `{{ key }}` / `{{ key.sub }}`, resolved by `Arr::get()` over the context
built in `buildContext()`:

| Placeholder                    | Value                                                    |
| ------------------------------ | -------------------------------------------------------- |
| `{{ field.<name> }}`           | submitted value of the field with that `name`             |
| `{{ files }}`                  | every uploaded file as markdown `- [original name](url)`  |
| `{{ form.id }}`, `.name`       | the form                                                  |
| `{{ submission.id }}`, `.created_at` | the submission                                      |
| `{{ submission.url }}`         | its edit page in the panel, `null` before the row is saved |
| `{{ submission.ip }}`, `.user_agent` | what the request carried                            |
| `{{ submission.status }}`      | enum value — **always `processing`** in a letter (see below) |

- **File fields**: `data` holds no file entries (`SubmitFormAction` keeps `data`
  and `uploads` apart), so `field.<name>` is filled from the `files` relation
  grouped by `field_name` and yields **URLs only**, several joined by `\n`.
  Names come only from `{{ files }}`.
- **Unknown key → empty string**, silently (`:70`). A typo in a placeholder
  costs a blank spot in the letter, not an error. So do `{{ form }}` and
  `{{ submission }}` on their own — an array renders as nothing.
- **`{{ submission.status }}` carries no information**: `SubmitFormAction:85`
  sets `Processing` before dispatching the job, and `Sent`/`Failed` is written
  only after the mail is out. Kept in the context for templates that already
  use it, left out of the panel hint.
- `{{ submission.url }}` is built from the route name, not from
  `FormSubmissionResource::getUrl()` — the worker has no current panel.
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

Under each `MarkdownEditor` sits a collapsed `Section` («Доступные
плейсхолдеры») holding a `Filament\Schemas\Components\Html` — the schema
component that takes an `HtmlString` straight into the tree. `$record` is
injectable into its closure like anywhere else (`Component.php:129`), and a
collapsed section still renders its children into the DOM (Alpine only hides
them), so `assertSee` in a Livewire test keeps working.

`FormForm::placeholdersHint()` builds two groups: `DEFAULT_PLACEHOLDERS` — a
const that mirrors `buildContext()`, so adding a context key has an obvious
second place to edit — and the form's own enabled fields, described by their
`label` rather than the slug. Labels of `checkbox`/`radio` are rich text, hence
`strip_tags` + `Str::limit(40)` in `fieldDescription()`.

Two constraints worth keeping:

- **Layout through inline `style`, not utility classes.** The theme collects
  classes with `@source '../../../../app/Filament/**/*'`, so a Tailwind class
  written here is dead until someone rebuilds the assets. Colours are left to
  inherit — the hint then needs to know nothing about the dark theme.
- **Lang strings are plain prose, markup lives in code.** Every value goes
  through `e()` before it reaches the `HtmlString`, so a `<` in a translation
  shows up as text instead of breaking the layout.

Attachments for the user letter are a separate thing: static files from
`user_mail_attachments`, not submission uploads.
