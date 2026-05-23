<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupExerciseController;
use App\Http\Controllers\GroupExercisePointsController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\WorkoutExerciseController;
use App\Http\Controllers\WorkoutExerciseLogController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding');

    Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::post('/groups/join', [GroupMemberController::class, 'join'])->name('groups.join');

    Route::middleware('group.member')->group(function () {
        Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');
        Route::get('/groups/{group}/edit', [GroupController::class, 'edit'])->name('groups.edit');
        Route::put('/groups/{group}', [GroupController::class, 'update'])->name('groups.update');
        Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');
        Route::post('/groups/{group}/invite-code/regenerate', [GroupController::class, 'regenerateInviteCode'])
            ->name('groups.invite-code.regenerate');

        Route::delete('/groups/{group}/leave', [GroupMemberController::class, 'leave'])->name('groups.leave');
        Route::delete('/groups/{group}/members/{user}', [GroupMemberController::class, 'destroy'])
            ->name('groups.members.destroy');
        Route::patch('/groups/{group}/members/{user}/role', [GroupMemberController::class, 'updateRole'])
            ->name('groups.members.role');

        Route::get('/groups/{group}/exercises', [GroupExerciseController::class, 'index'])
            ->name('groups.exercises.index');
        Route::post('/groups/{group}/exercises', [GroupExerciseController::class, 'store'])
            ->name('groups.exercises.store');
        Route::patch('/groups/{group}/exercises/{exercise}', [GroupExerciseController::class, 'update'])
            ->name('groups.exercises.update');
        Route::delete('/groups/{group}/exercises/{exercise}', [GroupExerciseController::class, 'destroy'])
            ->name('groups.exercises.destroy');

        Route::get('/groups/{group}/rubric', [GroupExercisePointsController::class, 'index'])
            ->name('groups.rubric.index');
        Route::put('/groups/{group}/rubric/{exercise}', [GroupExercisePointsController::class, 'store'])
            ->name('groups.rubric.store');

        Route::get('/workouts', [WorkoutController::class, 'index'])->name('workout.index');
        Route::get('/workout/create', [WorkoutController::class, 'create'])->name('workout.create');
        Route::post('/workout', [WorkoutController::class, 'store'])->name('workout.store');
        Route::get('/workout/{workout}', [WorkoutController::class, 'show'])->name('workout.show');
        Route::get('/workout/{workout}/edit', [WorkoutController::class, 'edit'])->name('workout.edit');
        Route::patch('/workout/{workout}', [WorkoutController::class, 'update'])->name('workout.update');
        Route::delete('/workout/{workout}', [WorkoutController::class, 'destroy'])->name('workout.destroy');

        Route::post(
            '/workout/{workout}/exercise',
            [WorkoutExerciseController::class, 'store']
        )->name('workout.exercise.store');
        Route::get(
            '/workout/{workout}/exercise/{workoutExercise}',
            [WorkoutExerciseController::class, 'show']
        )->name('workout.exercise.show');
        Route::delete(
            '/workout/{workout}/exercise/{workoutExercise}',
            [WorkoutExerciseController::class, 'destroy']
        )->name('workout.exercise.destroy');

        Route::post(
            '/workout/{workout}/exercise/{workoutExercise}/log',
            [WorkoutExerciseLogController::class, 'store']
        )->name('workout.exercise.log.store');
        Route::patch(
            '/workout/{workout}/exercise/{workoutExercise}/log/{log}',
            [WorkoutExerciseLogController::class, 'update']
        )->name('workout.exercise.log.update');
        Route::delete(
            '/workout/{workout}/exercise/{workoutExercise}/log/{log}',
            [WorkoutExerciseLogController::class, 'destroy']
        )->name('workout.exercise.log.destroy');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
