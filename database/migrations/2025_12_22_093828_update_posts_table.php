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
        if (!Schema::hasTable('posts')) {
            return;
        }
        
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'content')) {
                $table->dropColumn('content');
            }
            
            if (!Schema::hasColumn('posts', 'top_section')) {
                $table->json('top_section')->nullable();
            }
            
            if (!Schema::hasColumn('posts', 'main_section')) {
                $table->json('main_section')->nullable();
            }
            
            if (!Schema::hasColumn('posts', 'bottom_section')) {
                $table->json('bottom_section')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('posts')) {
            return;
        }
        
        Schema::table('posts', function (Blueprint $table) {
            if ( ! Schema::hasColumn('posts', 'content')) {
                $table->text('content')->nullable();
            }
            
            $toDrop = [];
            
            foreach (['top_section', 'main_section', 'bottom_section'] as $column) {
                if (Schema::hasColumn('posts', $column)) {
                    $toDrop[] = $column;
                }
            }
            
            if ($toDrop !== []) {
                $table->dropColumn($toDrop);
            }
        });
    }
};
