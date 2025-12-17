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
        Schema::create('global_settings', function (Blueprint $table) {
            $table->id()->from(1000);
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('email')->nullable();
            $table->string('favicon')->nullable(); // path from FileUpload
            
            $table->longText('code_header')->nullable();
            $table->longText('code_body_top')->nullable();
            $table->longText('code_body_bottom')->nullable();
            
            $table->timestamps();
        });
        
        DB::table('global_settings')->insert([
            'id' => 1,
            'title' => null,
            'description' => null,
            'email' => null,
            'favicon' => null,
            'code_header' => null,
            'code_body_top' => null,
            'code_body_bottom' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_settings');
    }
};
