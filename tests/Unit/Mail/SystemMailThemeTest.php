<?php

namespace Tests\Unit\Mail;

use App\Models\User;
use Filament\Auth\Notifications\ResetPassword;
use Tests\TestCase;

class SystemMailThemeTest extends TestCase
{
    private function renderResetPassword(): string
    {
        $this->app->setLocale('ru');

        $notification = new ResetPassword('token');
        $notification->url = 'https://example.test/reset/token';

        return (string) $notification->toMail(new User)->render();
    }

    public function test_notification_is_wrapped_in_the_branded_template(): void
    {
        $html = $this->renderResetPassword();

        $this->assertStringContainsString('images/email/hero.jpg', $html);
        $this->assertStringContainsString('ОГРН', $html);
        $this->assertStringContainsString('class="letter"', $html);
        $this->assertStringContainsString('background-color: #2f4a5f', $html);
        $this->assertStringContainsString('https://example.test/reset/token', $html);
    }

    public function test_body_is_translated(): void
    {
        $html = $this->renderResetPassword();

        $this->assertStringContainsString('Здравствуйте!', $html);
        $this->assertStringContainsString('Сбросить пароль', $html);
        $this->assertStringNotContainsString('Regards,', $html);
    }

    public function test_theme_does_not_leak_into_the_template_footer(): void
    {
        $html = $this->renderResetPassword();

        // The legal line of the footer carries a single inline property; the
        // theme is scoped to `.letter`, so nothing may be inlined on top of it.
        $this->assertStringContainsString('<p style="margin:0">', $html);
    }
}
