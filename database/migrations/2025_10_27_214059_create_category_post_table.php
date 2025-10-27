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
        Schema::create('category_post', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'post_id']);
        });
        
        if (Schema::hasColumn('posts', 'category_id')) {
            DB::statement(
                'INSERT IGNORE INTO category_post (category_id, post_id)
                           SELECT category_id, id FROM posts WHERE category_id IS NOT NULL'
            );
            
            Schema::table('posts', function (Blueprint $table) {
                $table->dropConstrainedForeignId('category_id');
            });
        }
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'category_id')) {
                $table
                    ->foreignId('category_id')
                    ->nullable()
                    ->constrained('categories')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }
        });
        
        Schema::dropIfExists('category_post');
    }
};
