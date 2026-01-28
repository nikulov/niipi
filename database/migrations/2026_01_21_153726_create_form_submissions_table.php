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
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id()->from(1000);
            
            $table->foreignId('form_id')
                ->constrained()
                ->cascadeOnDelete();
            
            $table->string('status')->default('new');
            
            $table->json('data');
            
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            
            $table->text('error_message')->nullable();
            
            $table->timestamps();
            
            $table->index(['form_id', 'status']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
