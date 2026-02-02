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
            $table->boolean('send_admin_mail')->default(true)->after('recipient_admin_email');
            $table->string('admin_mail_subject', 255)->nullable()->after('send_admin_mail');
            $table->longText('admin_mail_body_md')->nullable()->after('admin_mail_subject');
            
            $table->boolean('send_user_mail')->default(true)->after('admin_mail_body_md');
            $table->string('user_mail_subject', 255)->nullable()->after('send_user_mail');
            $table->longText('user_mail_body_md')->nullable()->after('user_mail_subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn([
                'send_admin_mail',
                'admin_mail_subject',
                'admin_mail_body_md',
                'send_user_mail',
                'user_mail_subject',
                'user_mail_body_md',
            ]);
        });
    }
};
