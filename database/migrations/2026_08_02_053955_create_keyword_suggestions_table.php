<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keyword_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('keyword');
            $table->string('source')->default('manual')->comment('manual | news-api');
            $table->string('source_url')->nullable();
            $table->text('context')->nullable()->comment('Headline/description the keyword came from');
            $table->string('suggested_type')->default('blog')->comment('blog | news | tool');
            $table->unsignedTinyInteger('relevance')->default(0)->comment('0-100, how well it fits this site\'s niche');
            $table->string('status')->default('new')->comment('new | used | dismissed');
            $table->foreignId('used_post_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->unique(['keyword', 'source']);
            $table->index(['status', 'relevance']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keyword_suggestions');
    }
};
