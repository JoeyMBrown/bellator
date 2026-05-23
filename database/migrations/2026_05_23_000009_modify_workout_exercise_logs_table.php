<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workout_exercise_logs', function (Blueprint $table) {
            $table->renameColumn('repitions', 'repetitions');
        });

        Schema::table('workout_exercise_logs', function (Blueprint $table) {
            $table->decimal('exercise_metric', 16, 2)->nullable()->change();
            $table->unsignedBigInteger('metric_unit_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('workout_exercise_logs', function (Blueprint $table) {
            $table->renameColumn('repetitions', 'repitions');
        });

        Schema::table('workout_exercise_logs', function (Blueprint $table) {
            $table->decimal('exercise_metric', 16, 2)->nullable(false)->change();
            $table->unsignedBigInteger('metric_unit_id')->nullable(false)->change();
        });
    }
};
