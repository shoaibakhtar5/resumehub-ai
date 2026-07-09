<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users', indexName: 'fk_media_upl_by_user')->nullOnDelete();
            $table->nullableMorphs('mediable', 'idx_media_mdbl');
            $table->string('disk', 40)->default('public');
            $table->string('directory')->nullable();
            $table->string('filename');
            $table->string('original_name');
            $table->string('mime_type', 160)->index('idx_media_mime_type');
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('checksum', 128)->nullable()->index('idx_media_chcksm');
            $table->string('visibility', 40)->default('public');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['disk', 'directory'], 'idx_media_disk_drctry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};