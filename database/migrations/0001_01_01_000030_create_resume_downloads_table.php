<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_downloads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resume_id')->constrained(indexName: 'fk_res_dls_res')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained(indexName: 'fk_res_dls_user')->nullOnDelete();
            $table->foreignId('resume_share_id')->nullable()->constrained(indexName: 'fk_res_dls_res_share')->nullOnDelete();
            $table->string('format', 40)->default('pdf')->index('idx_res_dls_format');
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('downloaded_at')->index('idx_res_dls_dwnldd_at');
            $table->timestamps();

            $table->index(['resume_id', 'downloaded_at'], 'idx_res_dls_res_dwnldd_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_downloads');
    }
};