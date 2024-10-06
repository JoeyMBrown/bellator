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
        Schema::create('workout_exercises', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40);
            $table->string('description');
            $table->unsignedBigInteger('exercise_id');
            $table->unsignedBigInteger('workout_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('exercise_id')->references('id')->on('exercises');
            $table->foreign('workout_id')->references('id')->on('workouts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_exercises');
    }
};
