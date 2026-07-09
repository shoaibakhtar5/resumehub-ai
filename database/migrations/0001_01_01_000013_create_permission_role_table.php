<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_role', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('permission_id')->constrained(indexName: 'fk_perm_role_perm')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained(indexName: 'fk_perm_role_role')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['permission_id', 'role_id'], 'uniq_perm_role_perm_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
    }
};