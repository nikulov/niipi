<?php

namespace Tests\Unit\View\Components\Other;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SocialIconTest extends TestCase
{
    public function test_inlines_the_icon_from_disk(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/footer/logo-vk.svg', '<svg id="vk"></svg>');

        $this->blade('<x-other.social-icon url="https://vk.com/x" icon-url="images/footer/logo-vk.svg" />')
            ->assertSee('href="https://vk.com/x"', false)
            ->assertSee('<svg id="vk"></svg>', false);
    }

    public function test_renders_the_link_when_the_icon_file_is_gone(): void
    {
        Storage::fake('public');

        $this->blade('<x-other.social-icon url="https://vk.com/x" icon-url="images/footer/gone.svg" />')
            ->assertSee('href="https://vk.com/x"', false)
            ->assertDontSee('<svg', false);
    }
}
