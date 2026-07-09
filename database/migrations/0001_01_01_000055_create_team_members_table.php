<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('photo_media_id')->nullable()->constrained('media', indexName: 'fk_team_members_photo_media')->nullOnDelete();
            $table->string('name');
            $table->string('role');
            $table->text('bio')->nullable();
            $table->string('email')->nullable();
            $table->json('social_links')->nullable();
            $table->boolean('is_active')->default(true)->index('idx_team_members_is_active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};