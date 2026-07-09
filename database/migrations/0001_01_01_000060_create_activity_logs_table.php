<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->nullableMorphs('causer', 'idx_act_logs_causer');
            $table->nullableMorphs('subject', 'idx_act_logs_subject');
            $table->string('event', 120)->index('idx_act_logs_event');
            $table->text('description')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->uuid('batch_uuid')->nullable()->index('idx_act_logs_batch_uuid');
            $table->timestamps();

            $table->index(['event', 'created_at'], 'idx_act_logs_event_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};