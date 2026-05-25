<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupExercisePointsRequest;
use App\Models\Exercise;
use App\Models\Group;
use App\Models\GroupExercisePoints;
use App\Models\WorkoutExerciseLogPoints;
use App\Services\GroupExercisePointsService;
use App\Services\PointsCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class GroupExercisePointsController extends Controller
{
    public function __construct(
        protected GroupExercisePointsService $pointsService,
        protected PointsCalculationService $calculator,
    ) {}

    public function index(Group $group): Response
    {
        $this->authorize('viewAny', [GroupExercisePoints::class, $group]);

        $activePoints = GroupExercisePoints::query()
            ->where('group_id', $group->id)
            ->whereNull('end_date')
            ->get()
            ->keyBy('exercise_id');

        $exercises = $group->exercises()
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'measurement_type'])
            ->map(function (Exercise $exercise) use ($activePoints, $group) {
                $points = $activePoints->get($exercise->id);

                return [
                    'id' => $exercise->id,
                    'name' => $exercise->name,
                    'measurement_type' => $exercise->measurement_type,
                    'points_per_unit' => $points?->points_per_unit !== null
                        ? (float) $points->points_per_unit
                        : null,
                    'pending_log_count' => $this->pendingLogCount($group, $exercise), // TODO: Rename to pendingSetCount here and on frontend.
                ];
            });

        return Inertia::render('Groups/Rubric/Index', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'role' => $group->roleFor(Auth::user()),
            ],
            'exercises' => $exercises,
        ]);
    }

    public function store(StoreGroupExercisePointsRequest $request, Group $group, Exercise $exercise): RedirectResponse
    {
        abort_if($exercise->group_id !== $group->id, 404);

        $this->authorize('create', [GroupExercisePoints::class, $group]);

        $ppu = $this->calculator->deriveFromAnchor($exercise->measurement_type, $request->validated());

        $this->pointsService->setPoints($group, $exercise, $ppu);

        return redirect()
            ->route('groups.rubric.index', $group)
            ->with('success', 'Points updated.');
    }

    // TODO: Rename to pendingSetCount here and on frontend.
    protected function pendingLogCount(Group $group, Exercise $exercise): int
    {
        return WorkoutExerciseLogPoints::query()
            ->whereNull('points_earned')
            ->where('group_id', $group->id)
            ->whereHas('workoutExerciseLog.workoutExercise', function ($query) use ($exercise) {
                $query->where('exercise_id', $exercise->id);
            })
            ->count();
    }
}
