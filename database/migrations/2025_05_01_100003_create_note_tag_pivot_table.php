<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('note_tag_pivot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained('notes');
            $table->foreignId('tag_id')->constrained('note_tags');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('note_tag_pivot');
    }
};
