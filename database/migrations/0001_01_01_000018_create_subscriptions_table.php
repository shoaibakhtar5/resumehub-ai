<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained(indexName: 'fk_subs_user')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained(indexName: 'fk_subs_team')->nullOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained(indexName: 'fk_subs_plan')->nullOnDelete();
            $table->string('provider', 40)->default('manual')->index('idx_subs_prvdr');
            $table->string('provider_customer_id')->nullable()->index('idx_subs_prvdr_cstmr');
            $table->string('provider_subscription_id')->nullable()->unique('uniq_subs_prvdr_sbscrpt');
            $table->string('status', 40)->default('active')->index('idx_subs_status');
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_starts_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable()->index('idx_subs_current_period_ends_at');
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'idx_subs_user_status');
            $table->index(['team_id', 'status'], 'idx_subs_team_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};