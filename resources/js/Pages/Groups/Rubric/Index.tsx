import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type MeasurementType =
    | 'reps_only'
    | 'weighted_reps'
    | 'distance'
    | 'duration';

interface RubricExercise {
    id: number;
    name: string;
    measurement_type: MeasurementType;
    points_per_unit: number | null;
    pending_log_count: number;
}

interface IndexProps {
    group: { id: number; name: string; role: 'owner' | 'admin' | 'member' | null };
    exercises: RubricExercise[];
}

const MEASUREMENT_LABELS: Record<MeasurementType, string> = {
    reps_only: 'Reps only',
    weighted_reps: 'Weighted reps',
    distance: 'Distance',
    duration: 'Duration',
};

export default function RubricIndex({ group, exercises }: IndexProps) {
    const canEdit = group.role === 'owner' || group.role === 'admin';

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between gap-3">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        {group.name} — Rubric
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
            <Head title={`${group.name} — Rubric`} />

            <div className="mx-auto max-w-3xl space-y-4 px-4 py-6 sm:px-6 lg:px-8">
                <p className="text-sm text-gray-700 dark:text-gray-300">
                    Set how many points each exercise is worth using a concrete example —
                    the per-unit rate is computed for you.
                </p>

                <ul className="space-y-3">
                    {exercises.map((exercise) => (
                        <li
                            key={exercise.id}
                            className="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800"
                        >
                            <div className="flex items-center justify-between">
                                <div>
                                    <div className="text-base font-semibold text-gray-900 dark:text-gray-100">
                                        {exercise.name}
                                    </div>
                                    <div className="text-xs uppercase text-gray-500">
                                        {MEASUREMENT_LABELS[exercise.measurement_type]}
                                    </div>
                                </div>
                                <div className="text-xs text-gray-500">
                                    {exercise.points_per_unit !== null
                                        ? `${exercise.points_per_unit.toFixed(4)} pts/unit`
                                        : 'Not set'}
                                </div>
                            </div>
                            {canEdit && (
                                <RubricForm groupId={group.id} exercise={exercise} />
                            )}
                        </li>
                    ))}
                </ul>
            </div>
        </AuthenticatedLayout>
    );
}

interface RubricFormProps {
    groupId: number;
    exercise: RubricExercise;
}

function RubricForm({ groupId, exercise }: RubricFormProps) {
    const initial = anchorDefaultsFor(exercise);

    const { data, setData, put, processing, errors } = useForm(initial);

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put(route('groups.rubric.store', [groupId, exercise.id]), {
            preserveScroll: true,
        });
    };

    return (
        <form onSubmit={submit} className="mt-3 space-y-2">
            <div className="flex flex-wrap items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                <NumberField
                    label="Points"
                    value={data.points}
                    onChange={(v) => setData('points', v)}
                />
                {exercise.measurement_type === 'reps_only' && (
                    <>
                        <span>for</span>
                        <NumberField
                            label="Reps"
                            value={data.reps ?? ''}
                            onChange={(v) => setData('reps', v)}
                        />
                        <span>reps</span>
                    </>
                )}
                {exercise.measurement_type === 'weighted_reps' && (
                    <>
                        <span>for</span>
                        <NumberField
                            label="Reps"
                            value={data.reps ?? ''}
                            onChange={(v) => setData('reps', v)}
                        />
                        <span>reps at</span>
                        <NumberField
                            label="Weight"
                            value={data.weight ?? ''}
                            onChange={(v) => setData('weight', v)}
                        />
                        <span>lbs</span>
                    </>
                )}
                {exercise.measurement_type === 'distance' && (
                    <>
                        <span>for</span>
                        <NumberField
                            label="Distance"
                            value={data.distance ?? ''}
                            onChange={(v) => setData('distance', v)}
                        />
                        <span>miles</span>
                    </>
                )}
                {exercise.measurement_type === 'duration' && (
                    <>
                        <span>for</span>
                        <NumberField
                            label="Seconds"
                            value={data.seconds ?? ''}
                            onChange={(v) => setData('seconds', v)}
                        />
                        <span>seconds</span>
                    </>
                )}
                <button
                    type="submit"
                    disabled={processing}
                    className="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500 disabled:opacity-50"
                >
                    Save
                </button>
            </div>
            {(errors.points || errors.reps || errors.weight || errors.distance || errors.seconds) && (
                <p className="text-sm text-red-600">
                    {errors.points || errors.reps || errors.weight || errors.distance || errors.seconds}
                </p>
            )}
            {exercise.pending_log_count > 0 && exercise.points_per_unit === null && (
                <p className="text-xs text-gray-500">
                    {exercise.pending_log_count} pending logs will receive points when you save.
                </p>
            )}
        </form>
    );
}

interface NumberFieldProps {
    label: string;
    value: number | string;
    onChange: (v: string) => void;
}

function NumberField({ label, value, onChange }: NumberFieldProps) {
    return (
        <input
            type="number"
            inputMode="decimal"
            step="any"
            min="0"
            aria-label={label}
            value={value}
            onChange={(e) => onChange(e.target.value)}
            className="w-20 rounded-md border-gray-300 px-2 py-1 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
        />
    );
}

function anchorDefaultsFor(exercise: RubricExercise): Record<string, string> {
    return {
        points: '',
        reps: '',
        weight: '',
        distance: '',
        seconds: '',
    };
}
