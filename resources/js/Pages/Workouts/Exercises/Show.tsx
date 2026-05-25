import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type MeasurementType =
    | 'reps_only'
    | 'weighted_reps'
    | 'distance'
    | 'duration';

interface MetricUnitOption {
    id: number;
    name: string;
}

interface LogRow {
    id: number;
    repetitions: number | null;
    exercise_metric: number | null;
    metric_unit_id: number | null;
    metric_unit_name: string | null;
}

interface ShowProps {
    workout: {
        id: number;
        workout_date: string;
        is_owner: boolean;
    };
    workoutExercise: {
        id: number;
        exercise: {
            id: number;
            name: string;
            measurement_type: MeasurementType;
        };
        logs: LogRow[];
    };
    metricUnitOptions: MetricUnitOption[];
}

export default function Show({ workout, workoutExercise, metricUnitOptions }: ShowProps) {
    const type = workoutExercise.exercise.measurement_type;
    const isOwner = workout.is_owner;

    const initialFormState = makeInitialState(type, metricUnitOptions);

    const { data, setData, post, processing, errors, reset } = useForm(initialFormState);

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(
            route('workout.exercise.log.store', [workout.id, workoutExercise.id]),
            {
                preserveScroll: true,
                onSuccess: () => reset(),
            },
        );
    };

    const deleteLog = (log: LogRow) => {
        if (!window.confirm('Remove this set?')) return;
        router.delete(
            route('workout.exercise.log.destroy', [workout.id, workoutExercise.id, log.id]),
            { preserveScroll: true },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between gap-3">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        {workoutExercise.exercise.name}
                    </h2>
                    <Link
                        href={route('workout.show', workout.id)}
                        className="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                    >
                        Back
                    </Link>
                </div>
            }
        >
            <Head title={workoutExercise.exercise.name} />

            <div className="mx-auto max-w-3xl space-y-4 px-4 py-6 sm:px-6 lg:px-8">
                <section className="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800">
                    <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Sets ({workoutExercise.logs.length})
                    </h3>
                    {workoutExercise.logs.length === 0 ? (
                        <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            No sets logged yet.
                        </p>
                    ) : (
                        <ul className="mt-2 divide-y divide-gray-100 dark:divide-gray-700">
                            {workoutExercise.logs.map((log) => (
                                <li
                                    key={log.id}
                                    className="flex items-center justify-between py-2 text-sm"
                                >
                                    <span className="text-gray-900 dark:text-gray-100">
                                        {formatLog(log, type)}
                                    </span>
                                    {isOwner && (
                                        <button
                                            type="button"
                                            onClick={() => deleteLog(log)}
                                            className="text-xs font-medium text-red-600 hover:text-red-500"
                                        >
                                            Remove
                                        </button>
                                    )}
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                {isOwner && (
                    <section className="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800">
                        <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Log a set
                        </h3>
                        <form onSubmit={submit} className="mt-3 space-y-3">
                            {type === 'reps_only' && (
                                <NumberField
                                    id="repetitions"
                                    label="Reps"
                                    value={data.repetitions ?? ''}
                                    onChange={(v) => setData('repetitions', v as any)}
                                    error={errors.repetitions}
                                />
                            )}
                            {type === 'weighted_reps' && (
                                <>
                                    <NumberField
                                        id="repetitions"
                                        label="Reps"
                                        value={data.repetitions ?? ''}
                                        onChange={(v) => setData('repetitions', v as any)}
                                        error={errors.repetitions}
                                    />
                                    <NumberField
                                        id="exercise_metric"
                                        label="Weight"
                                        value={data.exercise_metric ?? ''}
                                        onChange={(v) => setData('exercise_metric', v as any)}
                                        error={errors.exercise_metric}
                                    />
                                    <MetricUnitField
                                        value={data.metric_unit_id as number | ''}
                                        options={metricUnitOptions}
                                        onChange={(v) => setData('metric_unit_id', v as any)}
                                        error={errors.metric_unit_id}
                                    />
                                </>
                            )}
                            {type === 'distance' && (
                                <>
                                    <NumberField
                                        id="exercise_metric"
                                        label="Distance"
                                        value={data.exercise_metric ?? ''}
                                        onChange={(v) => setData('exercise_metric', v as any)}
                                        error={errors.exercise_metric}
                                    />
                                    <MetricUnitField
                                        value={data.metric_unit_id as number | ''}
                                        options={metricUnitOptions}
                                        onChange={(v) => setData('metric_unit_id', v as any)}
                                        error={errors.metric_unit_id}
                                    />
                                </>
                            )}
                            {type === 'duration' && (
                                <NumberField
                                    id="exercise_metric"
                                    label="Seconds"
                                    value={data.exercise_metric ?? ''}
                                    onChange={(v) => setData('exercise_metric', v as any)}
                                    error={errors.exercise_metric}
                                />
                            )}
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex h-11 items-center justify-center rounded-md bg-indigo-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
                            >
                                Log set
                            </button>
                        </form>
                    </section>
                )}
            </div>
        </AuthenticatedLayout>
    );
}

interface NumberFieldProps {
    id: string;
    label: string;
    value: number | string;
    onChange: (v: string) => void;
    error?: string;
}

function NumberField({ id, label, value, onChange, error }: NumberFieldProps) {
    return (
        <div>
            <label htmlFor={id} className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                {label}
            </label>
            <input
                id={id}
                type="number"
                inputMode="decimal"
                step="any"
                min="0"
                className="mt-1 block w-full rounded-md border-gray-300 px-3 py-2 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                value={value}
                onChange={(e) => onChange(e.target.value)}
            />
            {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
        </div>
    );
}

interface MetricUnitFieldProps {
    value: number | '';
    options: MetricUnitOption[];
    onChange: (v: number | '') => void;
    error?: string;
}

function MetricUnitField({ value, options, onChange, error }: MetricUnitFieldProps) {
    return (
        <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-200">Unit</label>
            <select
                className="mt-1 block w-full rounded-md border-gray-300 px-3 py-2 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                value={value}
                onChange={(e) => onChange(e.target.value === '' ? '' : Number(e.target.value))}
            >
                <option value="">Choose a unit</option>
                {options.map((unit) => (
                    <option key={unit.id} value={unit.id}>
                        {unit.name}
                    </option>
                ))}
            </select>
            {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
        </div>
    );
}

function makeInitialState(
    type: MeasurementType,
    metricUnitOptions: MetricUnitOption[],
): {
    repetitions: number | string;
    exercise_metric: number | string;
    metric_unit_id: number | string;
} {
    return {
        repetitions: type === 'reps_only' || type === 'weighted_reps' ? '' : 1,
        exercise_metric: '',
        metric_unit_id: type === 'duration' ? findUnitByName(metricUnitOptions, 'Seconds') ?? '' : '',
    };
}

function findUnitByName(options: MetricUnitOption[], name: string): number | null {
    const match = options.find((o) => o.name.toLowerCase() === name.toLowerCase());
    return match ? match.id : null;
}

function formatLog(log: LogRow, type: MeasurementType): string {
    switch (type) {
        case 'reps_only':
            return `${log.repetitions ?? 0} reps`;
        case 'weighted_reps':
            return `${log.repetitions ?? 0} × ${log.exercise_metric ?? 0} ${log.metric_unit_name ?? ''}`;
        case 'distance':
            return `${log.exercise_metric ?? 0} ${log.metric_unit_name ?? ''}`;
        case 'duration':
            return `${log.exercise_metric ?? 0} s`;
    }
}
