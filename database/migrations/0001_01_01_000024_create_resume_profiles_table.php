<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resume_id')->constrained(indexName: 'fk_res_prfls_res')->cascadeOnDelete();
            $table->string('full_name')->nullable();
            $table->string('headline')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('website')->nullable();
            $table->string('location')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code', 40)->nullable();
            $table->string('photo_path')->nullable();
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique('resume_id', 'uniq_res_prfls_res');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_profiles');
    }
};