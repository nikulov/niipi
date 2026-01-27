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
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id()->from(1000);
            
            $table->foreignId('form_id')
                ->constrained()
                ->cascadeOnDelete();
            
            $table->string('type');
            $table->string('name');
            $table->string('label');
            
            $table->boolean('required')->default(false);
            $table->boolean('is_enabled')->default(true);
            
            $table->unsignedInteger('sort')->default(0);
            
            $table->json('rules')->nullable();
            $table->json('options')->nullable();
            $table->json('extra')->nullable();
            
            $table->timestamps();
            
            $table->unique(['form_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
