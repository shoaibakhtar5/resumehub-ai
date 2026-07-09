<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ats_keywords', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ats_report_id')->constrained(indexName: 'fk_ats_kywrds_ats_report')->cascadeOnDelete();
            $table->string('keyword');
            $table->string('status', 40)->index('idx_ats_kywrds_status');
            $table->string('importance', 40)->default('medium')->index('idx_ats_kywrds_imprtnc');
            $table->unsignedSmallInteger('occurrences')->default(0);
            $table->text('suggestion')->nullable();
            $table->timestamps();

            $table->index(['ats_report_id', 'status'], 'idx_ats_kywrds_ats_report_status');
            $table->index('keyword', 'idx_ats_kywrds_keyword');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ats_keywords');
    }
};