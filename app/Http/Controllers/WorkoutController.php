<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutRequest;
use App\Models\Workout;
use App\Services\ExerciseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WorkoutController extends Controller
{
    protected $exerciseService;

    public function __construct(ExerciseService $exerciseService)
    {
        $this->exerciseService = $exerciseService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Workouts/Index', [
            'workouts' => Workout::orderBy('workout_date', 'DESC')->where('user_id', Auth::user()->id)->get(),
            // TODO: Abstract to service class
            // TODO: Create value object for workout_date that provides human
            // formatted string.
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Workouts/Create', []);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkoutRequest $request)
    {
        $data = $request->validated();

        // TODO: Abstract to service class
        Workout::create(array_merge($data, ['user_id' => Auth::getUser()->id]));

        return redirect()->route('dashboard')->with('success', 'Workout created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Inertia::render('Workouts/Show', [
            'workout' => Workout::with(['exercises' => function ($query) {
                $query->orderBy('workout_exercises.created_at', 'ASC');
            }])->find($id),
            'exerciseOptions' => $this->exerciseService->toOptionsArray()
            // TODO: Abstract any long queries to service class
            // TODO: Create value object for workout_date that provides human
            // formatted string.
            // TODO: Policy to restrict workouts belonging to logged in user.
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
