<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_certifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resume_id')->constrained(indexName: 'fk_res_certs_res')->cascadeOnDelete();
            $table->string('name');
            $table->string('issuer')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('credential_id')->nullable();
            $table->string('credential_url', 2048)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['resume_id', 'sort_order'], 'idx_res_certs_res_sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_certifications');
    }
};