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
        Schema::create('form_submission_files', function (Blueprint $table) {
            $table->id()->from(1000);
            
            $table->foreignId('form_submission_id')
                ->constrained('form_submissions')
                ->cascadeOnDelete();
            
            $table->string('field_name');     // e.g. "passport_file"
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            
            $table->timestamps();
            
            $table->index(['form_submission_id', 'field_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submission_files');
    }
};
