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
            $table->foreignId('workout_id')->constrained('workouts');
            $table->foreignId('exercise_id')->constrained('exercises');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['workout_id', 'exercise_id'])
                ->comment('Enforces each workout can only contain a reference to 1 of each exercise.');
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
