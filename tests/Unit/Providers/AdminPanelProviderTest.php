<?php

namespace Tests\Unit\Providers;

use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;
use Tests\TestCase;

class AdminPanelProviderTest extends TestCase
{
    public function test_configures_panel_id_and_path(): void
    {
        $provider = new AdminPanelProvider(app());
        $panel = $provider->panel(Panel::make());

        $this->assertSame('admin', $panel->getId());
        $this->assertSame('admin', $panel->getPath());
    }
}
