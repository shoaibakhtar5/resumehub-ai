<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->string('email')->unique('uniq_users_email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('profile_photo_path')->nullable();
            $table->string('phone', 40)->nullable()->index('idx_users_phone');
            $table->boolean('is_admin')->default(false)->index('idx_users_is_admin');
            $table->string('status', 40)->default('active')->index('idx_users_status');
            $table->string('timezone', 80)->default('UTC');
            $table->string('locale', 10)->default('en');
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};