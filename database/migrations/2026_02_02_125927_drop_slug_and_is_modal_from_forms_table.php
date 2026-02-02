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
        Schema::table('forms', function (Blueprint $table) {
            if (Schema::hasColumn('forms', 'slug')) {
                $table->dropColumn('slug');
            }
            
            if (Schema::hasColumn('forms', 'is_modal')) {
                $table->dropColumn('is_modal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            if (! Schema::hasColumn('forms', 'slug')) {
                $table->string('slug')->nullable();
            }
            
            if (! Schema::hasColumn('forms', 'is_modal')) {
                $table->boolean('is_modal')->default(false);
            }
        });
    }
};
