<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained(indexName: 'fk_ai_hist_user')->nullOnDelete();
            $table->foreignId('resume_id')->nullable()->constrained(indexName: 'fk_ai_hist_res')->cascadeOnDelete();
            $table->foreignId('ai_request_id')->nullable()->constrained(indexName: 'fk_ai_hist_ai_req')->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('feature', 80)->index('idx_ai_hist_feature');
            $table->string('action', 80)->index('idx_ai_hist_action');
            $table->longText('input')->nullable();
            $table->longText('output')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'feature', 'created_at'], 'idx_ai_hist_user_feature_created_at');
            $table->index(['resume_id', 'created_at'], 'idx_ai_hist_res_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_histories');
    }
};