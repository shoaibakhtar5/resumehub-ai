<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_skill', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resume_id')->constrained(indexName: 'fk_res_skill_res')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained(indexName: 'fk_res_skill_skill')->cascadeOnDelete();
            $table->string('category')->nullable()->index('idx_res_skill_cat');
            $table->string('proficiency', 60)->nullable();
            $table->decimal('years_experience', 4, 1)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['resume_id', 'skill_id'], 'uniq_res_skill_res_skill');
            $table->index(['resume_id', 'category', 'sort_order'], 'idx_res_skill_res_cat_sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_skill');
    }
};