<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('id')->constrained('groups');
            $table->enum('measurement_type', ['reps_only', 'weighted_reps', 'distance', 'duration'])
                ->nullable()
                ->after('description');
            $table->foreignUuid('created_by_user_id')->nullable()->after('measurement_type')->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_id');
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropColumn('measurement_type');
        });
    }
};
