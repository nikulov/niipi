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
        Schema::create('posts', function (Blueprint $table) {
            $table->id()->from(1001);
            
            $table->string('status')->default('draft')->index();
            $table->string('title', 500);
            $table->string('slug', 255)->unique();
            $table
                ->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            
            $table->json('content')->nullable();
            $table->string('thumbnail')->nullable();
            
            $table->string('meta_title', 500)->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
