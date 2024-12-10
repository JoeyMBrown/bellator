<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutExerciseLogRequest;
use App\Models\WorkoutExerciseLog;
// use App\Services\WorkoutExerciseLogService;
use Illuminate\Http\Request;

class WorkoutExerciseLogController extends Controller
{
    protected $workoutExerciseService;

    protected $metricUnitService;

    // TODO: Create service class

     public function __construct()
    {
        //
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
    public function store(StoreWorkoutExerciseLogRequest $request, $workout_id, $exercise_id)
    {
        $data = $request->validated(); // TODO: Update validation roles to ensure both the workout and exercise exist before storing.

        // TODO: Error handling of ALL controller methods.
        // TODO: Point calculation on storage.
        // TODO: Proper route redirection on save.

        WorkoutExerciseLog::create([
            'repitions' => $data['repitions'],
            'exercise_metric' => $data['exercise_metric'],
            'workout_exercise_id' => $exercise_id,
            'metric_unit_id' => $data['metric_unit_id']
        ]);

        return redirect()
            ->route('workout.exercise.show', [$workout_id, $exercise_id])
            ->with('success', 'Exercise logged successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $workout_id, string $exercise_id)
    {
        //
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
