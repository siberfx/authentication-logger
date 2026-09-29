<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('auth-logger.table_name', 'auth_logger'), function (Blueprint $table): void {
            $table->id();
            $table->morphs('authenticatable');
            $table->ipAddress()->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('login_at')->nullable()->index();
            $table->boolean('login_successful')->default(false);
            $table->timestamp('logout_at')->nullable();
            $table->boolean('cleared_by_user')->default(false);
            $table->json('location')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('auth-logger.table_name', 'auth_logger'));
    }
};
