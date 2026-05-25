import { Link, router } from '@inertiajs/react';
import dayjs from 'dayjs';

type Window = 'week' | 'month' | 'all';

interface LeaderboardRow {
    user_id: string;
    name: string;
    rank: number;
    total_points: number;
    workout_count: number;
    last_workout_date: string | null;
}

interface LeaderboardProps {
    groupId: number;
    window: Window;
    rows: LeaderboardRow[];
}

const WINDOW_LABELS: Record<Window, string> = {
    week: 'This Week',
    month: 'This Month',
    all: 'All Time',
};

export default function Leaderboard({ groupId, window, rows }: LeaderboardProps) {
    const switchWindow = (next: Window) => {
        router.get(
            route('groups.show', groupId),
            { window: next },
            {
                only: ['leaderboard'],
                preserveScroll: true,
                preserveState: true,
            },
        );
    };

    return (
        <section className="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800">
            <div className="flex items-center justify-between">
                <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Leaderboard
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

            <ul className="mt-3 divide-y divide-gray-100 dark:divide-gray-700">
                {rows.length === 0 ? (
                    <li className="py-3 text-sm text-gray-500">No members yet.</li>
                ) : (
                    rows.map((row) => (
                        <li key={row.user_id} className="py-2">
                            <Link
                                href={route('groups.members.show', [groupId, row.user_id])}
                                className="flex items-center justify-between gap-3 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 -mx-2 px-2 py-1 rounded"
                            >
                                <div className="flex items-center gap-3">
                                    <span className="w-6 text-xs font-mono text-gray-500">
                                        #{row.rank}
                                    </span>
                                    <Avatar name={row.name} />
                                    <div>
                                        <div className="font-medium text-gray-900 dark:text-gray-100">
                                            {row.name}
                                        </div>
                                        <div className="text-xs text-gray-500">
                                            {row.workout_count} {row.workout_count === 1 ? 'workout' : 'workouts'}
                                            {row.last_workout_date && (
                                                <span>
                                                    {' '}
                                                    · last{' '}
                                                    {dayjs(row.last_workout_date).format('MMM D')}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                </div>
                                <div className="text-sm font-semibold text-indigo-700 dark:text-indigo-300">
                                    {row.total_points.toFixed(1)}
                                </div>
                            </Link>
                        </li>
                    ))
                )}
            </ul>
        </section>
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
        <div className="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200">
            {initials || '?'}
        </div>
    );
}
