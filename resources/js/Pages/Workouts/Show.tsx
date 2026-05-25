import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import dayjs from 'dayjs';
import { FormEvent, useState } from 'react';

type MeasurementType =
    | 'reps_only'
    | 'weighted_reps'
    | 'distance'
    | 'duration';

interface WorkoutExerciseRow {
    id: number;
    name: string;
    measurement_type: MeasurementType;
    group_id: number;
    workout_exercise_id: number;
}

interface AvailableExercise {
    id: number;
    name: string;
    measurement_type: MeasurementType;
    group_id: number;
}

interface ShowProps {
    workout: {
        id: number;
        workout_date: string;
        notes: string | null;
        user_id: string;
        user_name: string | null;
        is_owner: boolean;
        groups: Array<{ id: number; name: string }>;
        exercises: WorkoutExerciseRow[];
    };
    availableExercises: AvailableExercise[];
}

const MEASUREMENT_LABELS: Record<MeasurementType, string> = {
    reps_only: 'Reps',
    weighted_reps: 'Weighted reps',
    distance: 'Distance',
    duration: 'Duration',
};

export default function Show({ workout, availableExercises }: ShowProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        exercise_id: '' as number | '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        if (data.exercise_id === '') return;
        post(route('workout.exercise.store', workout.id), {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    const deleteWorkout = () => {
        if (!window.confirm('Delete this workout? All logs will be removed.')) return;
        router.delete(route('workout.destroy', workout.id));
    };

    const removeExercise = (row: WorkoutExerciseRow) => {
        if (!window.confirm(`Remove "${row.name}" from this workout?`)) return;
        router.delete(route('workout.exercise.destroy', [workout.id, row.workout_exercise_id]), {
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between gap-3">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        {dayjs(workout.workout_date).format('ddd, MMM D, YYYY')}
                    </h2>
                    {workout.is_owner && (
                        <div className="flex gap-2">
                            <Link
                                href={route('workout.edit', workout.id)}
                                className="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                            >
                                Edit
                            </Link>
                            <button
                                type="button"
                                onClick={deleteWorkout}
                                className="text-sm font-medium text-red-600 hover:text-red-500"
                            >
                                Delete
                            </button>
                        </div>
                    )}
                </div>
            }
        >
            <Head title="Workout" />

            <div className="mx-auto max-w-3xl space-y-4 px-4 py-6 sm:px-6 lg:px-8">
                {!workout.is_owner && (
                    <div className="text-sm text-gray-600 dark:text-gray-400">
                        Logged by {workout.user_name ?? 'former member'}.
                    </div>
                )}

                <div className="flex flex-wrap gap-1">
                    {workout.groups.map((group) => (
                        <span
                            key={group.id}
                            className="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700"
                        >
                            {group.name}
                        </span>
                    ))}
                </div>

                {workout.notes && (
                    <p className="text-sm text-gray-700 dark:text-gray-300">{workout.notes}</p>
                )}

                <section className="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800">
                    <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Exercises ({workout.exercises.length})
                    </h3>
                    {workout.exercises.length === 0 ? (
                        <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            No exercises yet. Add one below.
                        </p>
                    ) : (
                        <ul className="mt-2 divide-y divide-gray-100 dark:divide-gray-700">
                            {workout.exercises.map((row) => (
                                <li
                                    key={row.workout_exercise_id}
                                    className="flex items-center justify-between py-3"
                                >
                                    <Link
                                        href={route('workout.exercise.show', [workout.id, row.workout_exercise_id])}
                                        className="text-sm font-medium text-indigo-700 hover:text-indigo-500"
                                    >
                                        {row.name}
                                        <span className="ml-2 text-xs uppercase text-gray-500">
                                            {MEASUREMENT_LABELS[row.measurement_type]}
                                        </span>
                                    </Link>
                                    {workout.is_owner && (
                                        <button
                                            type="button"
                                            onClick={() => removeExercise(row)}
                                            className="text-xs font-medium text-red-600 hover:text-red-500"
                                        >
                                            Remove
                                        </button>
                                    )}
                                </li>
                            ))}
                        </ul>
                    )}

                    {workout.is_owner && availableExercises.length > 0 && (
                        <form onSubmit={submit} className="mt-4 flex flex-col gap-2 sm:flex-row">
                            <select
                                aria-label="Add exercise"
                                className="flex-1 rounded-md border-gray-300 px-3 py-2 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                value={data.exercise_id}
                                onChange={(e) => setData('exercise_id', e.target.value === '' ? '' : Number(e.target.value))}
                            >
                                <option value="">Choose an exercise to add…</option>
                                {availableExercises.map((exercise) => (
                                    <option key={exercise.id} value={exercise.id}>
                                        {exercise.name} — {MEASUREMENT_LABELS[exercise.measurement_type]}
                                    </option>
                                ))}
                            </select>
                            <button
                                type="submit"
                                disabled={processing || data.exercise_id === ''}
                                className="h-11 rounded-md bg-indigo-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
                            >
                                Add
                            </button>
                        </form>
                    )}
                    {errors.exercise_id && (
                        <p className="mt-1 text-sm text-red-600">{errors.exercise_id}</p>
                    )}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
