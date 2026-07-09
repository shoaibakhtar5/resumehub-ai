<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_custom_section_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resume_custom_section_id')->constrained(indexName: 'fk_res_cust_sec_items_res_cust_sec')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('url', 2048)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->longText('description')->nullable();
            $table->json('fields')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['resume_custom_section_id', 'sort_order'], 'idx_res_cust_sec_items_res_cust_sec_sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_custom_section_items');
    }
};