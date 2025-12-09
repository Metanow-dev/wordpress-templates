<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name')->nullable();
            $table->string('demo_url')->nullable();
            $table->string('screenshot_url')->nullable();

            $table->string('language')->nullable();
            $table->string('active_theme')->nullable();
            $table->json('plugins')->nullable();

            $table->text('description_en')->nullable();
            $table->text('description_de')->nullable();
            $table->boolean('auto_translated')->default(false);

            $table->string('primary_category')->nullable()->index();
            $table->json('tags')->nullable();
            $table->decimal('classification_confidence', 4, 2)->nullable();
            $table->enum('classification_source', ['ai', 'human', 'manifest'])->default('ai');
            $table->text('classification_rationale')->nullable();

            $table->boolean('locked_by_human')->default(false);
            $table->boolean('needs_review')->default(false);

            $table->json('snippet_payload')->nullable();
            $table->char('text_snippet_hash', 64)->nullable();
            $table->char('last_classified_hash', 64)->nullable();

            $table->timestamp('last_scanned_at')->nullable();
            $table->timestamp('last_classified_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
