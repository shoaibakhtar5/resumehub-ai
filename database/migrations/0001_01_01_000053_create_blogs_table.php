<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blog_category_id')->nullable()->constrained(indexName: 'fk_blogs_blog_cat')->nullOnDelete();
            $table->foreignId('author_user_id')->nullable()->constrained('users', indexName: 'fk_blogs_auth_user')->nullOnDelete();
            $table->foreignId('cover_media_id')->nullable()->constrained('media', indexName: 'fk_blogs_cover_media')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique('uniq_blogs_slug');
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('status', 40)->default('draft')->index('idx_blogs_status');
            $table->timestamp('published_at')->nullable()->index('idx_blogs_pub_at');
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'published_at'], 'idx_blogs_status_pub_at');
            $table->fullText(['title', 'excerpt', 'content'], 'ft_blogs_title_excerpt_content');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};