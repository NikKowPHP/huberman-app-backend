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
        Schema::create('episode_protocol', function (Blueprint $table) {
            $table->foreignId('episode_id')->constrained()->onDelete('cascade');
            $table->foreignId('protocol_id')->constrained()->onDelete('cascade');
            $table->primary(['episode_id', 'protocol_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episode_protocol');
    }
};
