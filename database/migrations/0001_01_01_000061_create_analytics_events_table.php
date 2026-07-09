<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained(indexName: 'fk_ana_events_user')->nullOnDelete();
            $table->nullableMorphs('trackable', 'idx_ana_events_trckbl');
            $table->string('session_id')->nullable()->index('idx_ana_events_session');
            $table->string('event_name')->index('idx_ana_events_event_name');
            $table->string('event_category', 80)->nullable()->index('idx_ana_events_event_cat');
            $table->string('url', 2048)->nullable();
            $table->string('referrer', 2048)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->index('idx_ana_events_occrrd_at');
            $table->timestamps();

            $table->index(['event_name', 'occurred_at'], 'idx_ana_events_event_name_occrrd_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};