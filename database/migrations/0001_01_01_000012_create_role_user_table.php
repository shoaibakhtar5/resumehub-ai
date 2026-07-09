<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('role_id')->constrained(indexName: 'fk_role_user_role')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(indexName: 'fk_role_user_user')->cascadeOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users', indexName: 'fk_role_user_asgn_by_user')->nullOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'user_id'], 'uniq_role_user_role_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};