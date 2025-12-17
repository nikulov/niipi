<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id()->from(1000);
            $table->json('top_items')->nullable();
            $table->json('footer_items')->nullable();
            $table->timestamps();
        });
        
        DB::table('menus')->insert([
            'top_items' => json_encode([]),
            'footer_items' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
