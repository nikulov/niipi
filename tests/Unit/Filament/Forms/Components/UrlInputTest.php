<?php

namespace Tests\Unit\Filament\Forms\Components;

use App\Filament\Forms\Components\UrlInput;
use Tests\TestCase;

class UrlInputDouble extends UrlInput
{
    public static function publicNormalize(?string $value): ?string
    {
        return self::normalize($value);
    }
}

class UrlInputTest extends TestCase
{
    public function test_normalize_returns_null_for_blank(): void
    {
        $this->assertNull(UrlInputDouble::publicNormalize(null));
        $this->assertNull(UrlInputDouble::publicNormalize(''));
        $this->assertNull(UrlInputDouble::publicNormalize('   '));
    }

    public function test_normalize_keeps_absolute_urls(): void
    {
        $this->assertSame('http://example.com', UrlInputDouble::publicNormalize('http://example.com'));
        $this->assertSame('https://example.com/x', UrlInputDouble::publicNormalize('https://example.com/x'));
    }

    public function test_normalize_prepends_https_for_domain_like_input(): void
    {
        $this->assertSame('https://example.com/page', UrlInputDouble::publicNormalize('example.com/page'));
    }

    public function test_normalize_treats_leading_slash_and_slug_as_internal_path(): void
    {
        $expected = url('/some/page');

        $this->assertSame($expected, UrlInputDouble::publicNormalize('/some/page'));
        $this->assertSame($expected, UrlInputDouble::publicNormalize('some/page'));
    }

    public function test_setup_configures_prefix_and_max_length(): void
    {
        $input = UrlInput::make('url');

        $this->assertSame('niipigrad.ru/', $input->getPrefixLabel());
        $this->assertSame(255, $input->getMaxLength());
    }
}
