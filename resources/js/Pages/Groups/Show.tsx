import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import FeedCard from '@/Components/Groups/FeedCard';
import Leaderboard from '@/Components/Groups/Leaderboard';
import { Head, Link, router } from '@inertiajs/react';
import { Group } from '@/types';

type Window = 'week' | 'month' | 'all';

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

interface LeaderboardRow {
    user_id: string;
    name: string;
    rank: number;
    total_points: number;
    workout_count: number;
    last_workout_date: string | null;
}

interface ShowProps {
    group: Group;
    leaderboard: { window: Window; rows: LeaderboardRow[] };
    feed: {
        data: FeedWorkout[];
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
            has_more_pages: boolean;
        };
    };
}

export default function Show({ group, leaderboard, feed }: ShowProps) {
    const canManage = group.role === 'owner' || group.role === 'admin';

    const copyCode = () => {
        if (typeof navigator !== 'undefined' && navigator.clipboard) {
            navigator.clipboard.writeText(group.invite_code).catch(() => {});
        }
    };

    const loadMore = () => {
        router.get(
            route('groups.show', group.id),
            { window: leaderboard.window, feed_page: feed.meta.current_page + 1 },
            { preserveScroll: true, preserveState: false },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between gap-3">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        {group.name}
                    </h2>
                    {canManage && (
                        <Link
                            href={route('groups.edit', group.id)}
                            className="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                        >
                            Settings
                        </Link>
                    )}
                </div>
            }
        >
            <Head title={group.name} />

            <div className="mx-auto max-w-3xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                {group.description && (
                    <p className="text-sm text-gray-700 dark:text-gray-300">
                        {group.description}
                    </p>
                )}

                <div className="flex flex-wrap gap-2">
                    <Link
                        href={`${route('workout.create')}?group=${group.id}`}
                        className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        Log workout
                    </Link>
                    <Link
                        href={route('workout.index')}
                        className="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-100 dark:ring-gray-600"
                    >
                        My workouts
                    </Link>
                </div>

                <Leaderboard
                    groupId={group.id}
                    window={leaderboard.window}
                    rows={leaderboard.rows}
                />

                <section className="space-y-3">
                    <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Activity
                    </h3>
                    {feed.data.length === 0 ? (
                        <div className="rounded-lg bg-white p-4 text-sm text-gray-700 shadow-sm dark:bg-gray-800 dark:text-gray-300">
                            No workouts yet. Tap "Log workout" to start the feed.
                        </div>
                    ) : (
                        feed.data.map((workout) => (
                            <FeedCard key={workout.id} workout={workout} />
                        ))
                    )}
                    {feed.meta.has_more_pages && (
                        <button
                            type="button"
                            onClick={loadMore}
                            className="block w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                        >
                            Load more
                        </button>
                    )}
                </section>

                <section className="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800">
                    <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Invite code
                    </h3>
                    <div className="mt-2 flex items-center justify-between gap-3">
                        <code className="rounded bg-gray-100 px-2 py-1 font-mono text-base text-gray-900 dark:bg-gray-700 dark:text-gray-100">
                            {group.invite_code}
                        </code>
                        <button
                            type="button"
                            onClick={copyCode}
                            className="rounded-md bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-300"
                        >
                            Copy
                        </button>
                    </div>
                </section>

                <section className="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800">
                    <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Library
                    </h3>
                    <div className="mt-2 flex flex-wrap gap-2">
                        <Link
                            href={route('groups.exercises.index', group.id)}
                            className="rounded-md bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100"
                        >
                            Exercises
                        </Link>
                        <Link
                            href={route('groups.rubric.index', group.id)}
                            className="rounded-md bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100"
                        >
                            Rubric
                        </Link>
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
