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
        Schema::create('workout_exercise_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('repitions')
                ->comment('This can be used generally even when logging runs or swims (1 mile etc).');
            $table->decimal('exercise_metric', total: 16, places: 2);
            $table->foreignId('workout_exercise_id')->constrained('workout_exercises');
            $table->foreignId('metric_unit_id')->constrained('metric_units');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_exercise_logs');
    }
};
