import { Link } from '@inertiajs/react';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { useState } from 'react';

dayjs.extend(relativeTime);

type MeasurementType =
    | 'reps_only'
    | 'weighted_reps'
    | 'distance'
    | 'duration';

interface FeedLog {
    repetitions: number | null;
    exercise_metric: number | null;
    metric_unit_name: string | null;
}

interface FeedExercise {
    id: number;
    name: string;
    measurement_type: MeasurementType;
    logs: FeedLog[];
}

interface FeedWorkout {
    id: number;
    workout_date: string;
    notes: string | null;
    group_points_earned: number;
    user: { id: string | null; name: string };
    exercises: FeedExercise[];
}

interface FeedCardProps {
    workout: FeedWorkout;
}

const COLLAPSED_LIMIT = 3;

export default function FeedCard({ workout }: FeedCardProps) {
    const [expanded, setExpanded] = useState(false);
    const exercises = expanded
        ? workout.exercises
        : workout.exercises.slice(0, COLLAPSED_LIMIT);
    const hidden = Math.max(0, workout.exercises.length - COLLAPSED_LIMIT);

    return (
        <article className="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
            <div className="flex items-start justify-between gap-3">
                <div className="flex items-center gap-3">
                    <Avatar name={workout.user.name} />
                    <div>
                        <div className="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {workout.user.name}
                        </div>
                        <div
                            className="text-xs text-gray-500"
                            title={dayjs(workout.workout_date).format(
                                'YYYY-MM-DD HH:mm',
                            )}
                        >
                            {dayjs(workout.workout_date).fromNow()}
                        </div>
                    </div>
                </div>
                <div className="text-right text-sm font-semibold text-indigo-700 dark:text-indigo-300">
                    {workout.group_points_earned.toFixed(1)} pts
                </div>
            </div>

            <Link
                href={route('workout.show', workout.id)}
                className="mt-3 block space-y-1 text-sm text-gray-800 dark:text-gray-200"
            >
                {exercises.map((exercise) => (
                    <div key={exercise.id}>
                        <span className="font-medium">{exercise.name}</span>
                        <span className="text-gray-500"> — {consolidate(exercise)}</span>
                    </div>
                ))}
            </Link>

            {hidden > 0 && !expanded && (
                <button
                    type="button"
                    onClick={() => setExpanded(true)}
                    className="mt-2 text-xs font-medium text-indigo-700 hover:text-indigo-500"
                >
                    + {hidden} more
                </button>
            )}

            {workout.notes && (
                <p className="mt-3 text-xs text-gray-600 dark:text-gray-400">
                    {workout.notes}
                </p>
            )}
        </article>
    );
}

function Avatar({ name }: { name: string }) {
    const initials = name
        .split(/\s+/)
        .map((part) => part.charAt(0).toUpperCase())
        .filter(Boolean)
        .slice(0, 2)
        .join('');

    return (
        <div className="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200">
            {initials || '?'}
        </div>
    );
}

export function consolidate(exercise: FeedExercise): string {
    if (exercise.logs.length === 0) return 'no sets';

    switch (exercise.measurement_type) {
        case 'reps_only':
            return consolidateReps(exercise);
        case 'weighted_reps':
            return consolidateWeightedReps(exercise);
        case 'distance':
            return consolidateDistance(exercise);
        case 'duration':
            return consolidateDuration(exercise);
    }
}

function consolidateReps(exercise: FeedExercise): string {
    const reps = exercise.logs.map((l) => l.repetitions ?? 0);
    const setCount = reps.length;
    const allEqual = reps.every((r) => r === reps[0]);
    if (allEqual) {
        return `${setCount} × ${reps[0]}`;
    }
    return `${setCount} sets, ${Math.min(...reps)}–${Math.max(...reps)} reps`;
}

function consolidateWeightedReps(exercise: FeedExercise): string {
    const reps = exercise.logs.map((l) => l.repetitions ?? 0);
    const weights = exercise.logs.map((l) => l.exercise_metric ?? 0);
    const unit = exercise.logs[0]?.metric_unit_name ?? '';
    const allRepsEqual = reps.every((r) => r === reps[0]);
    const allWeightsEqual = weights.every((w) => w === weights[0]);

    if (allRepsEqual && allWeightsEqual) {
        return `${reps.length} × ${reps[0]} @ ${weights[0]} ${unit}`.trim();
    }
    if (allRepsEqual && !allWeightsEqual) {
        return `${reps.length} sets, ${Math.min(...weights)}–${Math.max(
            ...weights,
        )} ${unit}`.trim();
    }
    return `${reps.length} sets`;
}

function consolidateDistance(exercise: FeedExercise): string {
    const total = exercise.logs.reduce(
        (sum, l) => sum + (l.exercise_metric ?? 0),
        0,
    );
    const unit = exercise.logs[0]?.metric_unit_name ?? '';
    return `${formatNumber(total)} ${unit}`.trim();
}

function consolidateDuration(exercise: FeedExercise): string {
    const total = exercise.logs.reduce(
        (sum, l) => sum + (l.exercise_metric ?? 0),
        0,
    );
    return `${formatNumber(total)} s`;
}

function formatNumber(n: number): string {
    return Number.isInteger(n) ? String(n) : n.toFixed(2);
}
