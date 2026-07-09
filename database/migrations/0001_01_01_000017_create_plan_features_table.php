<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_features', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('plan_id')->constrained(indexName: 'fk_plan_ftrs_plan')->cascadeOnDelete();
            $table->string('feature_key');
            $table->string('feature_name');
            $table->json('value');
            $table->string('reset_interval', 40)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['plan_id', 'feature_key'], 'uniq_plan_ftrs_plan_feature_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_features');
    }
};