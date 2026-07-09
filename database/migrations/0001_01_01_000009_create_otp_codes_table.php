<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained(indexName: 'fk_otp_codes_user')->nullOnDelete();
            $table->string('identifier');
            $table->string('code_hash');
            $table->string('purpose', 40)->default('login');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at')->index('idx_otp_codes_expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['identifier', 'purpose', 'expires_at'], 'idx_otp_codes_idntfr_purpose_expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};