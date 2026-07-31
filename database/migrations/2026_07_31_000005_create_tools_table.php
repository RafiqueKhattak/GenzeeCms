<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('icon')->nullable();
            $table->string('component')->comment('Vue component name that renders the calculator, e.g. AgeCalculator');
            $table->text('short_description')->nullable();
            $table->longText('guide_content')->nullable()->comment('Rich-text guide/article shown below the calculator');
            $table->json('keywords')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('status')->default('draft'); // draft | published
            $table->unsignedInteger('order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
