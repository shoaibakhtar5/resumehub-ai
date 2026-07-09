<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_custom_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resume_id')->constrained(indexName: 'fk_res_cust_secs_res')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['resume_id', 'sort_order'], 'idx_res_cust_secs_res_sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_custom_sections');
    }
};