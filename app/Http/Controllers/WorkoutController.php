<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutRequest;
use App\Http\Requests\UpdateWorkoutRequest;
use App\Models\Workout;
use App\Services\WorkoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class WorkoutController extends Controller
{
    public function __construct(protected WorkoutService $workouts) {}

    public function index(): Response
    {
        $workouts = Workout::query()
            ->where('user_id', Auth::id())
            ->with('groups:id,name')
            ->orderByDesc('workout_date')
            ->get(['id', 'workout_date', 'notes', 'user_id']);

        return Inertia::render('Workouts/Index', [
            'workouts' => $workouts->map(fn (Workout $w) => [
                'id' => $w->id,
                'workout_date' => $w->workout_date,
                'notes' => $w->notes,
                'groups' => $w->groups->map(fn ($g) => ['id' => $g->id, 'name' => $g->name])->all(),
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Workout::class);

        return Inertia::render('Workouts/Create', [
            'availableGroups' => $this->availableGroupsForUser(),
            'defaultGroupId' => (int) request()->query('group') ?: null,
        ]);
    }

    public function store(StoreWorkoutRequest $request): RedirectResponse
    {
        $this->authorize('create', Workout::class);

        $workout = $this->workouts->create(Auth::user(), $request->validated());

        return redirect()
            ->route('workout.show', $workout)
            ->with('success', 'Workout created.');
    }

    public function show(Workout $workout): Response
    {
        $this->authorize('view', $workout);

        $workout->load([
            'groups:id,name',
            'exercises' => fn ($q) => $q->orderBy('workout_exercises.created_at'),
            'user:id,name',
        ]);

        $groupExercises = \App\Models\Exercise::query()
            ->whereIn('group_id', $workout->groups->pluck('id'))
            ->orderBy('name')
            ->get(['id', 'name', 'measurement_type', 'group_id']);

        return Inertia::render('Workouts/Show', [
            'workout' => [
                'id' => $workout->id,
                'workout_date' => $workout->workout_date,
                'notes' => $workout->notes,
                'user_id' => $workout->user_id,
                'user_name' => $workout->user?->name,
                'is_owner' => $workout->user_id === Auth::id(),
                'groups' => $workout->groups->map(fn ($g) => ['id' => $g->id, 'name' => $g->name])->all(),
                'exercises' => $workout->exercises->map(fn ($exercise) => [
                    'id' => $exercise->id,
                    'name' => $exercise->name,
                    'measurement_type' => $exercise->measurement_type,
                    'group_id' => $exercise->group_id,
                    'workout_exercise_id' => $exercise->pivot->id,
                ])->all(),
            ],
            'availableExercises' => $groupExercises,
        ]);
    }

    public function edit(Workout $workout): Response
    {
        $this->authorize('update', $workout);

        $workout->load('groups:id,name');

        return Inertia::render('Workouts/Edit', [
            'workout' => [
                'id' => $workout->id,
                'workout_date' => $workout->workout_date,
                'notes' => $workout->notes,
                'group_ids' => $workout->groups->pluck('id')->all(),
            ],
            'availableGroups' => $this->availableGroupsForUser(),
        ]);
    }

    public function update(UpdateWorkoutRequest $request, Workout $workout): RedirectResponse
    {
        $this->authorize('update', $workout);

        $this->workouts->update($workout, $request->validated());

        return redirect()
            ->route('workout.show', $workout)
            ->with('success', 'Workout updated.');
    }

    public function destroy(Workout $workout): RedirectResponse
    {
        $this->authorize('delete', $workout);

        $this->workouts->delete($workout);

        return redirect()
            ->route('workout.index')
            ->with('success', 'Workout deleted.');
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    protected function availableGroupsForUser(): array
    {
        return Auth::user()
            ->groups()
            ->orderBy('groups.name')
            ->get(['groups.id', 'groups.name'])
            ->map(fn ($g) => ['id' => $g->id, 'name' => $g->name])
            ->all();
    }
}
