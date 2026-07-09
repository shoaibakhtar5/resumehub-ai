<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique('uniq_res_uuid');
            $table->foreignId('user_id')->constrained(indexName: 'fk_res_user')->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained(indexName: 'fk_res_team')->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained(indexName: 'fk_res_tpl')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('status', 40)->default('draft')->index('idx_res_status');
            $table->string('source', 40)->default('builder');
            $table->string('target_role')->nullable();
            $table->string('target_company')->nullable();
            $table->string('language', 10)->default('en');
            $table->boolean('is_favorite')->default(false)->index('idx_res_is_fav');
            $table->boolean('is_archived')->default(false)->index('idx_res_is_archvd');
            $table->timestamp('archived_at')->nullable();
            $table->unsignedTinyInteger('completion_score')->default(0);
            $table->timestamp('last_autosaved_at')->nullable();
            $table->timestamp('last_exported_at')->nullable();
            $table->json('settings')->nullable();
            $table->text('search_text')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['user_id', 'slug'], 'uniq_res_user_slug');
            $table->index(['user_id', 'status', 'updated_at'], 'idx_res_user_status_updated_at');
            $table->index(['user_id', 'is_favorite', 'updated_at'], 'idx_res_user_is_fav_updated_at');
            $table->fullText(['title', 'search_text'], 'ft_res_title_search_text');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};