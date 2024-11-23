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
        Schema::create('exercise_points_history', function (Blueprint $table) {
            $table->id();
            $table->decimal('exercise_points', total: 8, places: 2);
            $table->decimal('exercise_metric', total: 16, places: 2);
            $table->foreignId('metric_unit_id')->constrained('metric_units');
            $table->foreignId('exercise_id')->constrained('exercises');
            $table->timestamp('start_date', precision: 0)->useCurrent();
            $table->timestamp('end_date', precision: 0)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_points_history');
    }
};
