<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_shares', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resume_id')->constrained(indexName: 'fk_res_shares_res')->cascadeOnDelete();
            $table->string('token', 128)->unique('uniq_res_shares_token');
            $table->string('slug')->nullable()->unique('uniq_res_shares_slug');
            $table->string('visibility', 40)->default('unlisted')->index('idx_res_shares_vsblty');
            $table->string('password_hash')->nullable();
            $table->boolean('allow_download')->default(false);
            $table->boolean('is_active')->default(true)->index('idx_res_shares_is_active');
            $table->timestamp('expires_at')->nullable()->index('idx_res_shares_expires_at');
            $table->timestamp('last_accessed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_shares');
    }
};