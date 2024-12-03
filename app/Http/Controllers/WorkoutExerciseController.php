<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutExerciseRequest;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Services\WorkoutExerciseService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkoutExerciseController extends Controller
{
    protected $workoutExerciseService;

    // TODO: Create service class

    // public function __construct(WorkoutExerciseService $workoutExerciseService)
    // {
    //     $this->workoutExerciseService = $workoutExerciseService;
    // }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkoutExerciseRequest $request, $id)
    {
        $data = $request->validated(); // TODO: Update validation roles to ensure both the workout and exercise exist before storing.

        // TODO: Abstract to service class
        // TODO: Error handling of ALL controller methods
        $workout = Workout::find($id);

        // This associates an exercise with the current workout.
        $workout->exercises()->attach($data['id']);

        return redirect()->route('workout.show', $id)->with('success', 'Exercise add to Workout.');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $workout_id, string $exercise_id)
    {
        return Inertia::render('Workouts/Exercises/Show', [
            'workoutExercises' => WorkoutExercise::with([
                    'workoutExerciseLogs.metricUnit',
                    'exercise',
                    'workout'
                ])
                ->where('exercise_id', $exercise_id)
                ->where('workout_id', $workout_id)
                ->firstOrFail(),
            // TODO: Abstract to service class
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
