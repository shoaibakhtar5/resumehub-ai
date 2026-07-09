<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users', indexName: 'fk_skills_created_by_user')->nullOnDelete();
            $table->string('name')->unique('uniq_skills_name');
            $table->string('slug')->unique('uniq_skills_slug');
            $table->string('category')->nullable()->index('idx_skills_cat');
            $table->boolean('is_verified')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skills');
    }
};