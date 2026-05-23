import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

type MeasurementType =
    | 'reps_only'
    | 'weighted_reps'
    | 'distance'
    | 'duration';

interface ExerciseRow {
    id: number;
    name: string;
    description: string | null;
    measurement_type: MeasurementType;
    created_by_user_id: string;
    can_edit: boolean;
}

interface IndexProps {
    group: { id: number; name: string; role: 'owner' | 'admin' | 'member' | null };
    exercises: ExerciseRow[];
    measurementTypes: MeasurementType[];
}

const MEASUREMENT_LABELS: Record<MeasurementType, string> = {
    reps_only: 'Reps only',
    weighted_reps: 'Weighted reps',
    distance: 'Distance',
    duration: 'Duration (seconds)',
};

export default function ExercisesIndex({ group, exercises, measurementTypes }: IndexProps) {
    const [editingId, setEditingId] = useState<number | null>(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        description: '',
        measurement_type: 'weighted_reps' as MeasurementType,
    });

    const submitCreate = (e: FormEvent) => {
        e.preventDefault();
        post(route('groups.exercises.store', group.id), {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    const deleteExercise = (exercise: ExerciseRow) => {
        if (!window.confirm(`Remove "${exercise.name}" from this group's exercise library?`)) {
            return;
        }
        router.delete(route('groups.exercises.destroy', [group.id, exercise.id]), {
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between gap-3">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        {group.name} — Exercises
                    </h2>
                    <Link
                        href={route('groups.edit', group.id)}
                        className="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                    >
                        Back to settings
                    </Link>
                </div>
            }
        >
            <Head title={`${group.name} — Exercises`} />

            <div className="mx-auto max-w-3xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <section className="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3 className="text-base font-semibold text-gray-900 dark:text-gray-100">
                        Add an exercise
                    </h3>
                    <form onSubmit={submitCreate} className="mt-4 space-y-3">
                        <div>
                            <label htmlFor="name" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                Name
                            </label>
                            <input
                                id="name"
                                type="text"
                                maxLength={60}
                                required
                                className="mt-1 block w-full rounded-md border-gray-300 px-3 py-2 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                            />
                            {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                        </div>

                        <div>
                            <label htmlFor="description" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                Description <span className="text-xs text-gray-500">(optional)</span>
                            </label>
                            <input
                                id="description"
                                type="text"
                                maxLength={140}
                                className="mt-1 block w-full rounded-md border-gray-300 px-3 py-2 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                            />
                            {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
                        </div>

                        <div>
                            <label htmlFor="measurement_type" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                Measurement type
                            </label>
                            <select
                                id="measurement_type"
                                className="mt-1 block w-full rounded-md border-gray-300 px-3 py-2 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                value={data.measurement_type}
                                onChange={(e) => setData('measurement_type', e.target.value as MeasurementType)}
                            >
                                {measurementTypes.map((type) => (
                                    <option key={type} value={type}>
                                        {MEASUREMENT_LABELS[type]}
                                    </option>
                                ))}
                            </select>
                            {errors.measurement_type && (
                                <p className="mt-1 text-sm text-red-600">{errors.measurement_type}</p>
                            )}
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="inline-flex h-11 items-center justify-center rounded-md bg-indigo-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
                        >
                            Add exercise
                        </button>
                    </form>
                </section>

                <section className="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3 className="text-base font-semibold text-gray-900 dark:text-gray-100">
                        Exercises ({exercises.length})
                    </h3>
                    <ul className="mt-3 divide-y divide-gray-100 dark:divide-gray-700">
                        {exercises.map((exercise) => (
                            <li key={exercise.id} className="py-3">
                                {editingId === exercise.id ? (
                                    <ExerciseEditRow
                                        groupId={group.id}
                                        exercise={exercise}
                                        measurementTypes={measurementTypes}
                                        onDone={() => setEditingId(null)}
                                    />
                                ) : (
                                    <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <div className="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {exercise.name}
                                            </div>
                                            {exercise.description && (
                                                <div className="text-xs text-gray-600 dark:text-gray-400">
                                                    {exercise.description}
                                                </div>
                                            )}
                                            <div className="mt-1 text-xs uppercase text-gray-500">
                                                {MEASUREMENT_LABELS[exercise.measurement_type]}
                                            </div>
                                        </div>
                                        {exercise.can_edit && (
                                            <div className="flex gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() => setEditingId(exercise.id)}
                                                    className="rounded-md bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-100"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => deleteExercise(exercise)}
                                                    className="rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100"
                                                >
                                                    Remove
                                                </button>
                                            </div>
                                        )}
                                    </div>
                                )}
                            </li>
                        ))}
                    </ul>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}

interface EditRowProps {
    groupId: number;
    exercise: ExerciseRow;
    measurementTypes: MeasurementType[];
    onDone: () => void;
}

function ExerciseEditRow({ groupId, exercise, measurementTypes, onDone }: EditRowProps) {
    const { data, setData, patch, processing, errors } = useForm({
        name: exercise.name,
        description: exercise.description ?? '',
        measurement_type: exercise.measurement_type,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        patch(route('groups.exercises.update', [groupId, exercise.id]), {
            preserveScroll: true,
            onSuccess: () => onDone(),
        });
    };

    return (
        <form onSubmit={submit} className="space-y-3">
            <input
                type="text"
                maxLength={60}
                required
                className="block w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                value={data.name}
                onChange={(e) => setData('name', e.target.value)}
            />
            <input
                type="text"
                maxLength={140}
                placeholder="Description"
                className="block w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                value={data.description}
                onChange={(e) => setData('description', e.target.value)}
            />
            <select
                className="block w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                value={data.measurement_type}
                onChange={(e) => setData('measurement_type', e.target.value as MeasurementType)}
            >
                {measurementTypes.map((type) => (
                    <option key={type} value={type}>
                        {MEASUREMENT_LABELS[type]}
                    </option>
                ))}
            </select>
            {errors.measurement_type && (
                <p className="text-sm text-red-600">{errors.measurement_type}</p>
            )}
            <div className="flex gap-2">
                <button
                    type="submit"
                    disabled={processing}
                    className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50"
                >
                    Save
                </button>
                <button
                    type="button"
                    onClick={onDone}
                    className="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
                >
                    Cancel
                </button>
            </div>
        </form>
    );
}
