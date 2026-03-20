<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update existing password_reset_tokens table
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            if (!Schema::hasColumn('password_reset_tokens', 'attempts')) {
                $table->tinyInteger('attempts')->default(0)->unsigned();
            }
        });

        // 2. Table password_history (anti-reuse)
        Schema::create('password_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('password_hash');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'created_at']);
        });

        // 3. Table password_change_log (audit)
        Schema::create('password_change_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('method', [
                'forgot_password',
                'user_change',
                'admin_reset',
                'forced_reset',
                'initial_set',
                'group_recovery_request'
            ]);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('all_sessions_revoked')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });

        // 4. Update users table with security columns
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('password');
            }
            if (!Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('must_change_password');
            }
            if (!Schema::hasColumn('users', 'reset_attempts')) {
                $table->tinyInteger('reset_attempts')->default(0)->unsigned()->after('password_changed_at');
            }
            if (!Schema::hasColumn('users', 'reset_locked_until')) {
                $table->timestamp('reset_locked_until')->nullable()->after('reset_attempts');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['must_change_password', 'password_changed_at', 'reset_attempts', 'reset_locked_until']);
        });
        Schema::dropIfExists('password_change_log');
        Schema::dropIfExists('password_history');
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropColumn('attempts');
        });
    }
};
