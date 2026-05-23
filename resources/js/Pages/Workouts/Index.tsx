import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import dayjs from 'dayjs';

interface WorkoutRow {
    id: number;
    workout_date: string;
    notes: string | null;
    groups: Array<{ id: number; name: string }>;
}

interface IndexProps {
    workouts: WorkoutRow[];
}

export default function Index({ workouts }: IndexProps) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between gap-3">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        My Workouts
                    </h2>
                    <Link
                        href={route('workout.create')}
                        className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        Log workout
                    </Link>
                </div>
            }
        >
            <Head title="My Workouts" />

            <div className="mx-auto max-w-3xl space-y-3 px-4 py-6 sm:px-6 lg:px-8">
                {workouts.length === 0 ? (
                    <div className="rounded-lg bg-white p-6 text-sm text-gray-700 shadow-sm dark:bg-gray-800 dark:text-gray-300">
                        You haven't logged any workouts yet.
                    </div>
                ) : (
                    workouts.map((workout) => (
                        <Link
                            key={workout.id}
                            href={route('workout.show', workout.id)}
                            className="block rounded-lg bg-white p-4 shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700"
                        >
                            <div className="flex items-center justify-between gap-3">
                                <div>
                                    <div className="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {dayjs(workout.workout_date).format('ddd, MMM D, YYYY')}
                                    </div>
                                    {workout.notes && (
                                        <div className="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                            {workout.notes}
                                        </div>
                                    )}
                                </div>
                                <div className="flex flex-wrap justify-end gap-1">
                                    {workout.groups.map((group) => (
                                        <span
                                            key={group.id}
                                            className="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700"
                                        >
                                            {group.name}
                                        </span>
                                    ))}
                                </div>
                            </div>
                        </Link>
                    ))
                )}
            </div>
        </AuthenticatedLayout>
    );
}
