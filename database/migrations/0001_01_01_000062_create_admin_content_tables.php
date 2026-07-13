<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->string('status', 40)->default('draft')->index();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('discount_type', 40)->default('percent');
            $table->unsignedInteger('discount_value')->default(0);
            $table->unsignedInteger('max_redemptions')->nullable();
            $table->unsignedInteger('redemptions')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('provider', 40)->default('manual');
            $table->string('type', 40)->default('payment');
            $table->string('status', 40)->default('completed')->index();
            $table->unsignedInteger('amount_cents')->default(0);
            $table->char('currency', 3)->default('USD');
            $table->timestamp('paid_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('email_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->string('subject');
            $table->longText('body');
            $table->boolean('is_active')->default(true)->index();
            $table->json('variables')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('pages');
    }
};
