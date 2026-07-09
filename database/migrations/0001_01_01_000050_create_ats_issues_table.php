<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ats_issues', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ats_report_id')->constrained(indexName: 'fk_ats_issues_ats_report')->cascadeOnDelete();
            $table->string('category', 80)->index('idx_ats_issues_cat');
            $table->string('severity', 40)->default('medium')->index('idx_ats_issues_svrty');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('suggestion')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['ats_report_id', 'severity'], 'idx_ats_issues_ats_report_svrty');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ats_issues');
    }
};