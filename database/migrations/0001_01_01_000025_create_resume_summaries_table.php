<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_summaries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resume_id')->constrained(indexName: 'fk_res_sums_res')->cascadeOnDelete();
            $table->longText('content')->nullable();
            $table->string('tone', 80)->nullable();
            $table->unsignedSmallInteger('word_count')->default(0);
            $table->boolean('ai_generated')->default(false);
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique('resume_id', 'uniq_res_sums_res');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_summaries');
    }
};