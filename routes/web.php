<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\WorkoutExerciseController;
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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard/Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function (){
    Route::get('/workout/create', [WorkoutController::class, 'create'])->name('workout.create');
    Route::get('/workouts', [WorkoutController::class, 'index'])->name('workout.index');
    Route::post('/workout', [WorkoutController::class, 'store'])->name('workout.store');
    Route::get('/workout/{id}', [WorkoutController::class, 'show'])->name('workout.show');

    Route::post('/workout/{id}/exercise', [WorkoutExerciseController::class, 'store'])->name('workout.exercise.store');
    Route::get('/workout/{workout_id}/exercise/{exercise_id}', [WorkoutExerciseController::class, 'show'])->name('workout.exercise.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
