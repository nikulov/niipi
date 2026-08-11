@php
    // The branded wrapper draws its own header, card and footer, so Laravel's
    // $header and $footer slots stay unused. The subcopy (fallback link under
    // the button) is part of the message body here.
    $letter =
        '<div class="letter">' .
        Illuminate\Mail\Markdown::parse((string) $slot) .
        (string) ($subcopy ?? '') .
        '</div>';
@endphp

@include('emails.email-template', [
    'body' => $letter,
    // A date in the card corner makes sense for a form submission, not for a
    // password reset.
    'date' => '',
])