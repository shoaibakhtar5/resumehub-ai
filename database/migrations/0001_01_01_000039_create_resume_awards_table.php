<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_awards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resume_id')->constrained(indexName: 'fk_res_awards_res')->cascadeOnDelete();
            $table->string('title');
            $table->string('issuer')->nullable();
            $table->date('awarded_at')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['resume_id', 'sort_order'], 'idx_res_awards_res_sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_awards');
    }
};