<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_id')->constrained('workouts');
            $table->foreignId('group_id')->constrained('groups');
            $table->timestamps();

            $table->unique(['workout_id', 'group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_groups');
    }
};
