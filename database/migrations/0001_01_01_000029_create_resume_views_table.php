<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resume_id')->constrained(indexName: 'fk_res_views_res')->cascadeOnDelete();
            $table->foreignId('resume_share_id')->nullable()->constrained(indexName: 'fk_res_views_res_share')->nullOnDelete();
            $table->foreignId('viewer_user_id')->nullable()->constrained('users', indexName: 'fk_res_views_viewer_user')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referrer', 2048)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->timestamp('viewed_at')->index('idx_res_views_viewed_at');
            $table->timestamps();

            $table->index(['resume_id', 'viewed_at'], 'idx_res_views_res_viewed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_views');
    }
};