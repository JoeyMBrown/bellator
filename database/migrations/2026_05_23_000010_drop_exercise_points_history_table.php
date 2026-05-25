<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('exercise_points_history');
    }

    public function down(): void
    {
        Schema::create('exercise_points_history', function (Blueprint $table) {
            $table->id();
            $table->decimal('exercise_points', 8, 2);
            $table->decimal('exercise_metric', 16, 2);
            $table->foreignId('metric_unit_id')->constrained('metric_units');
            $table->foreignId('exercise_id')->constrained('exercises');
            $table->timestamp('start_date')->useCurrent();
            $table->timestamp('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
