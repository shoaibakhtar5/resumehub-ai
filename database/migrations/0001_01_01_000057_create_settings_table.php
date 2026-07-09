<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users', indexName: 'fk_sets_updated_by_user')->nullOnDelete();
            $table->string('group', 80)->index('idx_sets_group');
            $table->string('key');
            $table->json('value')->nullable();
            $table->string('type', 40)->default('string');
            $table->boolean('is_public')->default(false)->index('idx_sets_is_public');
            $table->string('description')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['group', 'key'], 'uniq_sets_group_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};