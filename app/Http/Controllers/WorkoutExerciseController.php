<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutExerciseRequest;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Services\MetricUnitService;
// use App\Services\WorkoutExerciseService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkoutExerciseController extends Controller
{
    protected $workoutExerciseService;

    protected $metricUnitService;

    // TODO: Create service class

     public function __construct(MetricUnitService $metricUnitService)
    {
    //     $this->workoutExerciseService = $workoutExerciseService;
        $this->metricUnitService = $metricUnitService;
    }

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

        return redirect()
            ->route('workout.show', $id)
            ->with('success', 'Exercise added to Workout.');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $workout_id, string $exercise_id)
    {
        // TODO: Abstract to service class
        
        return Inertia::render('Workouts/Exercises/Show', [
            'workoutExercise' => WorkoutExercise::with([
                    'workoutExerciseLogs.metricUnit',
                    'exercise',
                    'workout'
                ])
                ->find($exercise_id),
            'metricUnitOptions' => $this->metricUnitService->toOptionsArray()
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
