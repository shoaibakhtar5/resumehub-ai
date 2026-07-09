<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_language', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resume_id')->constrained(indexName: 'fk_res_lang_res')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained(indexName: 'fk_res_lang_lang')->cascadeOnDelete();
            $table->string('proficiency', 80)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['resume_id', 'language_id'], 'uniq_res_lang_res_lang');
            $table->index(['resume_id', 'sort_order'], 'idx_res_lang_res_sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_language');
    }
};