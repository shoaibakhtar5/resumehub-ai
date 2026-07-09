<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users', indexName: 'fk_teams_owner_user')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique('uniq_teams_slug');
            $table->boolean('personal_team')->default(false);
            $table->string('status', 40)->default('active')->index('idx_teams_status');
            $table->json('settings')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};