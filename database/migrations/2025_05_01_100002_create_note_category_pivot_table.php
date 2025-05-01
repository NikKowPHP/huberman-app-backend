<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('note_category_pivot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained('notes');
            $table->foreignId('category_id')->constrained('note_categories');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('note_category_pivot');
    }
};
