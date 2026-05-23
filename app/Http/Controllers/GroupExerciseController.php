<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupExerciseRequest;
use App\Http\Requests\UpdateGroupExerciseRequest;
use App\Models\Exercise;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class GroupExerciseController extends Controller
{
    public function index(Group $group): Response
    {
        $this->authorize('viewAny', [Exercise::class, $group]);

        $exercises = $group->exercises()
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'measurement_type', 'created_by_user_id'])
            ->map(fn (Exercise $exercise) => [
                'id' => $exercise->id,
                'name' => $exercise->name,
                'description' => $exercise->description,
                'measurement_type' => $exercise->measurement_type,
                'created_by_user_id' => $exercise->created_by_user_id,
                'can_edit' => $this->canEdit(Auth::user(), $exercise, $group),
            ]);

        return Inertia::render('Groups/Exercises/Index', [
            'group' => $this->presentGroup($group),
            'exercises' => $exercises,
            'measurementTypes' => Exercise::MEASUREMENT_TYPES,
        ]);
    }

    public function store(StoreGroupExerciseRequest $request, Group $group): RedirectResponse
    {
        $this->authorize('create', [Exercise::class, $group]);

        Exercise::create([
            'group_id' => $group->id,
            'created_by_user_id' => Auth::id(),
            ...$request->validated(),
        ]);

        return redirect()
            ->route('groups.exercises.index', $group)
            ->with('success', 'Exercise added.');
    }

    public function update(UpdateGroupExerciseRequest $request, Group $group, Exercise $exercise): RedirectResponse
    {
        abort_if($exercise->group_id !== $group->id, 404);

        $this->authorize('update', $exercise);

        $data = $request->validated();
        $exercise->fill([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        if (! empty($data['measurement_type'])) {
            $exercise->measurement_type = $data['measurement_type'];
        }

        $exercise->save();

        return redirect()
            ->route('groups.exercises.index', $group)
            ->with('success', 'Exercise updated.');
    }

    public function destroy(Group $group, Exercise $exercise): RedirectResponse
    {
        abort_if($exercise->group_id !== $group->id, 404);

        $this->authorize('delete', $exercise);

        $exercise->delete();

        return redirect()
            ->route('groups.exercises.index', $group)
            ->with('success', 'Exercise removed.');
    }

    protected function canEdit($user, Exercise $exercise, Group $group): bool
    {
        $role = $group->roleFor($user);

        if (in_array($role, ['owner', 'admin'], true)) {
            return true;
        }

        return $role === 'member' && $exercise->created_by_user_id === $user->id;
    }

    protected function presentGroup(Group $group): array
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'role' => $group->roleFor(Auth::user()),
        ];
    }
}
