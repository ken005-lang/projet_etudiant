<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update group_profiles table
        DB::table('group_profiles')
            ->where('leader_sector', 'info')
            ->update(['leader_sector' => 'Informatique']);

        // Update group_members table
        DB::table('group_members')
            ->where('sector', 'info')
            ->update(['sector' => 'Informatique']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert group_profiles table
        DB::table('group_profiles')
            ->where('leader_sector', 'Informatique')
            ->update(['leader_sector' => 'info']);

        // Revert group_members table
        DB::table('group_members')
            ->where('sector', 'Informatique')
            ->update(['sector' => 'info']);
    }
};
