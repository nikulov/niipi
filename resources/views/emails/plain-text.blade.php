{{-- text/plain part: no HTML is executed here, and `{{ }}` would turn the
     already-rendered `&` and quotes into entities. The comment sits on the same
     line as the echo so the body does not start with a blank line. --}}{!! $textBody !!}
