<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('template_category_id')->nullable()->constrained(indexName: 'fk_tpls_tpl_cat')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users', indexName: 'fk_tpls_created_by_user')->nullOnDelete();
            $table->foreignId('package_media_id')->nullable()->constrained('media', indexName: 'fk_tpls_package_media')->nullOnDelete();
            $table->foreignId('preview_media_id')->nullable()->constrained('media', indexName: 'fk_tpls_preview_media')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique('uniq_tpls_slug');
            $table->text('description')->nullable();
            $table->string('status', 40)->default('draft')->index('idx_tpls_status');
            $table->string('version', 40)->default('1.0.0');
            $table->string('package_path')->nullable();
            $table->string('preview_path')->nullable();
            $table->string('entry_html')->default('resume.html');
            $table->string('entry_css')->default('style.css');
            $table->json('config')->nullable();
            $table->boolean('is_premium')->default(false)->index('idx_tpls_is_premium');
            $table->unsignedInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->fullText(['name', 'description'], 'ft_tpls_name_dscrptn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};