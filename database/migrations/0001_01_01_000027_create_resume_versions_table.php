<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resume_id')->constrained(indexName: 'fk_res_vers_res')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users', indexName: 'fk_res_vers_created_by_user')->nullOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('label')->nullable();
            $table->string('reason', 60)->default('manual')->index('idx_res_vers_reason');
            $table->json('snapshot');
            $table->string('content_hash', 128)->nullable()->index('idx_res_vers_content_hash');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['resume_id', 'version_number'], 'uniq_res_vers_res_ver_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_versions');
    }
};