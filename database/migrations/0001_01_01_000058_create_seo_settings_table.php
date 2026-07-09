<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_settings', function (Blueprint $table): void {
            $table->id();
            $table->nullableMorphs('seoable', 'idx_seo_sets_seoable');
            $table->foreignId('image_media_id')->nullable()->constrained('media', indexName: 'fk_seo_sets_image_media')->nullOnDelete();
            $table->string('page_key')->nullable()->unique('uniq_seo_sets_page_key');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('keywords')->nullable();
            $table->string('canonical_url', 2048)->nullable();
            $table->string('robots', 120)->nullable();
            $table->json('schema_json')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id'], 'uniq_seo_sets_seoable_type_seoable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_settings');
    }
};