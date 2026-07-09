<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique('uniq_plans_slug');
            $table->text('description')->nullable();
            $table->unsignedInteger('price_cents')->default(0);
            $table->char('currency', 3)->default('USD');
            $table->string('billing_interval', 40)->default('month');
            $table->unsignedSmallInteger('trial_days')->default(0);
            $table->boolean('is_active')->default(true)->index('idx_plans_is_active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};