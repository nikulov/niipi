<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_files', function (Blueprint $table) {
            $table->id()->startingValue(1001);

            $table->string('path')->unique();
            $table->string('disk')->default('public');
            $table->string('filename');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('type')->default('other');

            $table->string('title')->nullable();
            $table->string('alt')->nullable();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('type');
            $table->index('mime_type');
        });

        Schema::create('media_file_usages', function (Blueprint $table) {
            $table->id()->startingValue(1001);

            $table->foreignId('media_file_id')
                ->constrained('media_files')
                ->cascadeOnDelete();

            $table->morphs('usable');
            $table->string('field');

            $table->timestamps();

            $table->unique(
                ['media_file_id', 'usable_type', 'usable_id', 'field'],
                'media_file_usages_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_file_usages');
        Schema::dropIfExists('media_files');
    }
};