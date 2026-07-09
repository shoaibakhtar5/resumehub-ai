<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users', indexName: 'fk_cont_msgs_asgn_to_user')->nullOnDelete();
            $table->string('name');
            $table->string('email')->index('idx_cont_msgs_email');
            $table->string('phone', 40)->nullable();
            $table->string('subject')->nullable();
            $table->longText('message');
            $table->string('status', 40)->default('new')->index('idx_cont_msgs_status');
            $table->string('source', 80)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'created_at'], 'idx_cont_msgs_status_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};