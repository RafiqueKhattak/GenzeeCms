<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-request view log for the public site.
     *
     * Deliberately stores NO raw IP address and no per-visitor identifier —
     * only a coarse country code derived from the request and then discarded.
     * That keeps the table useful for "which pages are working" analysis
     * without turning it into personal data that would need a retention
     * policy and a GDPR lawful basis.
     */
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('subject_type')->nullable()->comment('tool | blog | news | page | other');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->char('country_code', 2)->nullable()->comment('Derived from CDN/proxy geo header; null when unavailable');
            $table->string('referrer_host')->nullable();
            $table->boolean('is_bot')->default(false);
            $table->timestamp('viewed_at')->index();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['path', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
