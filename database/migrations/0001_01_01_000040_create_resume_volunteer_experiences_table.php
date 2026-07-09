<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_volunteer_experiences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resume_id')->constrained(indexName: 'fk_res_vol_exps_res')->cascadeOnDelete();
            $table->string('organization');
            $table->string('role')->nullable();
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->longText('description')->nullable();
            $table->json('highlights')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['resume_id', 'sort_order'], 'idx_res_vol_exps_res_sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_volunteer_experiences');
    }
};