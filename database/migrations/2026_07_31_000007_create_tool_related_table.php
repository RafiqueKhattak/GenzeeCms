<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tool_related', function (Blueprint $table) {
            $table->foreignId('tool_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_tool_id')->constrained('tools')->cascadeOnDelete();
            $table->unsignedInteger('order')->default(0);
            $table->primary(['tool_id', 'related_tool_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_related');
    }
};
