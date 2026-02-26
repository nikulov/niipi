<?php

namespace Database\Seeders;

use App\Models\Footer;
use Illuminate\Database\Seeder;

class FooterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Footer::query()->firstOrCreate(
            ['id' => 1001],
            [
                'contact_data' => '',
                'social_data' => [],
            ]
        );
    }
}