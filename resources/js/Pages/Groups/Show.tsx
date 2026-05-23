import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Group } from '@/types';

interface ShowProps {
    group: Group;
}

export default function Show({ group }: ShowProps) {
    const canManage = group.role === 'owner' || group.role === 'admin';

    const copyCode = () => {
        if (typeof navigator !== 'undefined' && navigator.clipboard) {
            navigator.clipboard.writeText(group.invite_code).catch(() => {});
        }
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
                        Members ({group.members.length})
                    </h3>
                    <ul className="mt-2 divide-y divide-gray-100 dark:divide-gray-700">
                        {group.members.map((member) => (
                            <li
                                key={member.id}
                                className="flex items-center justify-between py-2 text-sm"
                            >
                                <span className="text-gray-900 dark:text-gray-100">
                                    {member.name}
                                </span>
                                <span className="text-xs uppercase text-gray-500">
                                    {member.role}
                                </span>
                            </li>
                        ))}
                    </ul>
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

                <section className="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800">
                    <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Leaderboard & feed
                    </h3>
                    <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        The leaderboard and activity feed will appear here once
                        workouts are logged to this group.
                    </p>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
