<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_exercise_log_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_exercise_log_id')->constrained('workout_exercise_logs');
            $table->foreignId('group_id')->constrained('groups');
            $table->decimal('points_earned', 14, 4)->nullable();
            $table->timestamps();

            $table->unique(['workout_exercise_log_id', 'group_id'], 'welp_log_group_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_exercise_log_points');
    }
};
