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
        Schema::create('pages', function (Blueprint $table) {
            $table->id()->from(1000);
            $table->string('title', 255);
            $table->string('slug', 255)->unique()->index();
            $table->string('status', 50)->default('draft')->index();
            
            $table->timestamp('published_at')->nullable()->index();
            
            $table->json('top_section')->nullable();
            $table->json('main_section')->nullable();
            $table->json('bottom_section')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
