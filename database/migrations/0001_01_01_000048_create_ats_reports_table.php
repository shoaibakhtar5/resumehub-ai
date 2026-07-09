<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ats_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained(indexName: 'fk_ats_reports_user')->nullOnDelete();
            $table->foreignId('resume_id')->nullable()->constrained(indexName: 'fk_ats_reports_res')->cascadeOnDelete();
            $table->foreignId('uploaded_media_id')->nullable()->constrained('media', indexName: 'fk_ats_reports_upl_media')->nullOnDelete();
            $table->string('source', 40)->default('resumehub')->index('idx_ats_reports_source');
            $table->string('target_job_title')->nullable();
            $table->longText('job_description')->nullable();
            $table->decimal('ats_score', 5, 2)->default(0);
            $table->decimal('keyword_score', 5, 2)->default(0);
            $table->decimal('formatting_score', 5, 2)->default(0);
            $table->decimal('content_score', 5, 2)->default(0);
            $table->decimal('readability_score', 5, 2)->default(0);
            $table->string('status', 40)->default('completed')->index('idx_ats_reports_status');
            $table->json('raw_result')->nullable();
            $table->timestamp('scanned_at')->index('idx_ats_reports_scanned_at');
            $table->timestamps();

            $table->index(['user_id', 'scanned_at'], 'idx_ats_reports_user_scanned_at');
            $table->index(['resume_id', 'scanned_at'], 'idx_ats_reports_res_scanned_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ats_reports');
    }
};