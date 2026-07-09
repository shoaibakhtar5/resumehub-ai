<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained(indexName: 'fk_ai_reqs_user')->nullOnDelete();
            $table->foreignId('resume_id')->nullable()->constrained(indexName: 'fk_ai_reqs_res')->cascadeOnDelete();
            $table->string('provider', 40)->default('gemini')->index('idx_ai_reqs_prvdr');
            $table->string('model')->nullable();
            $table->string('feature', 80)->index('idx_ai_reqs_feature');
            $table->string('action', 80)->index('idx_ai_reqs_action');
            $table->string('prompt_hash', 128)->nullable()->index('idx_ai_reqs_prompt_hash');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('status', 40)->default('pending')->index('idx_ai_reqs_status');
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->unsignedInteger('latency_ms')->nullable();
            $table->decimal('cost_estimate', 10, 6)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'action', 'created_at'], 'idx_ai_reqs_user_action_created_at');
            $table->index(['resume_id', 'feature'], 'idx_ai_reqs_res_feature');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_requests');
    }
};