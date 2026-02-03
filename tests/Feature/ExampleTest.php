<?php

namespace Tests\Feature;

use App\Enums\PageStatus;
use App\Models\Footer;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        Page::create([
            'title' => 'Home',
            'slug' => 'home',
            'status' => PageStatus::Published->value,
            'published_at' => now()->subMinute(),
        ]);

        Footer::create([
            'contact_data' => '',
            'social_data' => [],
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
