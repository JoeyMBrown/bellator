import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import FeedCard from '@/Components/Groups/FeedCard';
import { Head, Link, router } from '@inertiajs/react';
import dayjs from 'dayjs';

type Window = 'week' | 'month' | 'all';
type Role = 'owner' | 'admin' | 'member';

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

interface RecentWorkout {
    id: number;
    workout_date: string;
    notes: string | null;
    group_points_earned: number;
    exercises: FeedExercise[];
}

interface ShowProps {
    group: { id: number; name: string };
    profile: {
        user_id: string;
        name: string | null;
        role: Role | null;
        member_since: string | null;
    };
    window: Window;
    windowStats: {
        total_points: number;
        workout_count: number;
        rank: number | null;
    };
    aggregate: {
        total_points: number;
        workout_count: number;
    };
    recentWorkouts: RecentWorkout[];
}

const WINDOW_LABELS: Record<Window, string> = {
    week: 'This Week',
    month: 'This Month',
    all: 'All Time',
};

export default function MemberProfile({
    group,
    profile,
    window,
    windowStats,
    aggregate,
    recentWorkouts,
}: ShowProps) {
    const switchWindow = (next: Window) => {
        router.get(
            route('groups.members.show', [group.id, profile.user_id]),
            { window: next },
            { preserveScroll: true, preserveState: false },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between gap-3">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        {profile.name ?? 'Former member'}
                    </h2>
                    <Link
                        href={route('groups.show', group.id)}
                        className="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                    >
                        Back to {group.name}
                    </Link>
                </div>
            }
        >
            <Head title={`${profile.name ?? 'Member'} — ${group.name}`} />

            <div className="mx-auto max-w-3xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <section className="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800">
                    <div className="flex items-center gap-4">
                        <Avatar name={profile.name ?? '?'} />
                        <div>
                            <div className="text-base font-semibold text-gray-900 dark:text-gray-100">
                                {profile.name ?? 'Former member'}
                            </div>
                            <div className="text-xs uppercase text-gray-500">
                                {profile.role ?? 'former member'}
                                {profile.member_since && (
                                    <span>
                                        {' · '}member since{' '}
                                        {dayjs(profile.member_since).format('MMM YYYY')}
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>
                </section>

                <section className="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800">
                    <div className="flex items-center justify-between">
                        <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Window stats
                        </h3>
                        <div className="flex rounded-md bg-gray-100 p-0.5 text-xs dark:bg-gray-700">
                            {(['week', 'month', 'all'] as Window[]).map((w) => (
                                <button
                                    key={w}
                                    type="button"
                                    onClick={() => switchWindow(w)}
                                    className={
                                        'rounded px-2 py-1 ' +
                                        (w === window
                                            ? 'bg-white font-semibold text-gray-900 shadow-sm dark:bg-gray-900 dark:text-gray-100'
                                            : 'text-gray-600 dark:text-gray-300')
                                    }
                                >
                                    {WINDOW_LABELS[w]}
                                </button>
                            ))}
                        </div>
                    </div>

                    <dl className="mt-3 grid grid-cols-3 gap-3 text-center">
                        <Stat label="Points" value={windowStats.total_points.toFixed(1)} />
                        <Stat label="Workouts" value={String(windowStats.workout_count)} />
                        <Stat
                            label="Rank"
                            value={windowStats.rank !== null ? `#${windowStats.rank}` : '—'}
                        />
                    </dl>
                </section>

                <section className="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800">
                    <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        All-time in {group.name}
                    </h3>
                    <dl className="mt-3 grid grid-cols-2 gap-3 text-center">
                        <Stat label="Total points" value={aggregate.total_points.toFixed(1)} />
                        <Stat label="Total workouts" value={String(aggregate.workout_count)} />
                    </dl>
                </section>

                <section className="space-y-3">
                    <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Recent workouts
                    </h3>
                    {recentWorkouts.length === 0 ? (
                        <div className="rounded-lg bg-white p-4 text-sm text-gray-700 shadow-sm dark:bg-gray-800 dark:text-gray-300">
                            No workouts logged yet.
                        </div>
                    ) : (
                        recentWorkouts.map((workout) => (
                            <FeedCard
                                key={workout.id}
                                workout={{
                                    ...workout,
                                    user: {
                                        id: profile.user_id,
                                        name: profile.name ?? 'Former member',
                                    },
                                }}
                            />
                        ))
                    )}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}

function Stat({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <dt className="text-xs uppercase text-gray-500">{label}</dt>
            <dd className="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
                {value}
            </dd>
        </div>
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
        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 text-base font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200">
            {initials || '?'}
        </div>
    );
}
