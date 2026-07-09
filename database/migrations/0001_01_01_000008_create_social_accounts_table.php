<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained(indexName: 'fk_soc_accnts_user')->cascadeOnDelete();
            $table->string('provider', 40);
            $table->string('provider_user_id');
            $table->string('provider_email')->nullable();
            $table->string('provider_avatar_url', 2048)->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->json('raw_profile')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_user_id'], 'uniq_soc_accnts_prvdr_prvdr_user');
            $table->index(['user_id', 'provider'], 'idx_soc_accnts_user_prvdr');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};