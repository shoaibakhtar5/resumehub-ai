<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_tag', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blog_id')->constrained(indexName: 'fk_blog_tag_blog')->cascadeOnDelete();
            $table->foreignId('blog_tag_id')->constrained(indexName: 'fk_blog_tag_blog_tag')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['blog_id', 'blog_tag_id'], 'uniq_blog_tag_blog_blog_tag');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_tag');
    }
};