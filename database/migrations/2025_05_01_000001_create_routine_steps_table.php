<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('routine_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_id')->constrained();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration');
            $table->integer('order');
            $table->boolean('is_optional')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('routine_steps');
    }
};
