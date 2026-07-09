<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resume_id')->constrained(indexName: 'fk_res_secs_res')->cascadeOnDelete();
            $table->string('section_key');
            $table->string('title');
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['resume_id', 'section_key'], 'uniq_res_secs_res_sec_key');
            $table->index(['resume_id', 'is_visible', 'sort_order'], 'idx_res_secs_res_is_visible_sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_sections');
    }
};