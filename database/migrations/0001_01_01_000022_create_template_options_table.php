<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('template_id')->constrained(indexName: 'fk_tpl_options_tpl')->cascadeOnDelete();
            $table->string('type', 40)->index('idx_tpl_options_type');
            $table->string('key');
            $table->string('label');
            $table->json('value');
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['template_id', 'type', 'key'], 'uniq_tpl_options_tpl_type_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_options');
    }
};