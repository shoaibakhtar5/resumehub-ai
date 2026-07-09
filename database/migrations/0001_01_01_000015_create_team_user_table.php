<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained(indexName: 'fk_team_user_team')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(indexName: 'fk_team_user_user')->cascadeOnDelete();
            $table->string('role', 60)->default('member')->index('idx_team_user_role');
            $table->string('status', 40)->default('active')->index('idx_team_user_status');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'user_id'], 'uniq_team_user_team_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_user');
    }
};