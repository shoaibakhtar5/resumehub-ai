<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_social_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resume_id')->constrained(indexName: 'fk_res_soc_links_res')->cascadeOnDelete();
            $table->string('platform', 80);
            $table->string('label')->nullable();
            $table->string('url', 2048);
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['resume_id', 'sort_order'], 'idx_res_soc_links_res_sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_social_links');
    }
};