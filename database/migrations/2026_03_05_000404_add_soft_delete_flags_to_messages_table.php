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
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_deleted_by_visitor')->default(false)->after('is_read_by_visitor');
            $table->boolean('is_deleted_by_group')->default(false)->after('is_read_by_visitor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['is_deleted_by_visitor', 'is_deleted_by_group']);
        });
    }
};
