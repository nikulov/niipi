<?php

namespace Tests\Unit\Filament\Components;

use App\Filament\Components\ShareButton;
use Tests\TestCase;

class ShareButtonTest extends TestCase
{
    public function test_stored_socials_keep_the_icon_path_as_a_string(): void
    {
        foreach (ShareButton::defaultSocials() as $social) {
            $this->assertIsString($social['iconUrl']);
        }

        $data = ShareButton::getDefaultBlock('/news', 'Все новости')[0]['data'];

        $this->assertSame(ShareButton::defaultSocials(), $data['socials']);
        $this->assertTrue($data['showCopy']);
    }

    /**
     * The repeater default lands in raw state without passing through `FileUploadStateCast`,
     * and a `FileUpload` keeps raw state as an array — a bare string breaks the form.
     */
    public function test_form_socials_wrap_the_icon_path_in_an_array(): void
    {
        $formState = ShareButton::defaultSocialsAsFormState();

        $this->assertCount(count(ShareButton::defaultSocials()), $formState);

        foreach ($formState as $index => $social) {
            $stored = ShareButton::defaultSocials()[$index];

            $this->assertSame([$stored['iconUrl']], $social['iconUrl']);
            $this->assertSame($stored['title'], $social['title']);
            $this->assertSame($stored['shareUrl'], $social['shareUrl']);
        }
    }
}
