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
        Schema::table('group_profiles', function (Blueprint $table) {
            $table->string('contact_whatsapp')->nullable()->after('project_video');
            $table->string('contact_email')->nullable()->after('contact_whatsapp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_profiles', function (Blueprint $table) {
            $table->dropColumn(['contact_whatsapp', 'contact_email']);
        });
    }
};
