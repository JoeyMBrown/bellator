<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_exercise_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups');
            $table->foreignId('exercise_id')->constrained('exercises');
            $table->decimal('points_per_unit', 14, 6);
            $table->timestamp('start_date')->useCurrent();
            $table->timestamp('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['group_id', 'exercise_id', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_exercise_points');
    }
};
