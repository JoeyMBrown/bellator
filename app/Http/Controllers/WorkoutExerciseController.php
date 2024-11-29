<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutExerciseRequest;
use App\Models\WorkoutExercise;
use App\Services\WorkoutExerciseService;
use Illuminate\Http\Request;

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
        $data = $request->validated();

        // TODO: Abstract to service class
        // TODO: Clean this up
        WorkoutExercise::create(['workout_id' => $id, 'exercise_id' => $data['id']]);

        return redirect()->route('workout.show', $id)->with('success', 'Workout created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
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
