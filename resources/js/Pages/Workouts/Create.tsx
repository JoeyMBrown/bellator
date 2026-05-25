import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import dayjs from 'dayjs';
import { FormEvent } from 'react';

interface CreateProps {
    availableGroups: Array<{ id: number; name: string }>;
    defaultGroupId: number | null;
}

export default function Create({ availableGroups, defaultGroupId }: CreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        workout_date: dayjs().format('YYYY-MM-DD'),
        notes: '',
        group_ids: defaultGroupId !== null
            ? [defaultGroupId]
            : availableGroups.length > 0
                ? [availableGroups[0].id]
                : [],
    });

    const toggleGroup = (id: number) => {
        setData(
            'group_ids',
            data.group_ids.includes(id)
                ? data.group_ids.filter((g) => g !== id)
                : [...data.group_ids, id],
        );
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(route('workout.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Log a workout
                </h2>
            }
        >
            <Head title="Log a workout" />

            <div className="mx-auto max-w-xl px-4 py-6 sm:px-6 lg:px-8">
                <form
                    onSubmit={submit}
                    className="space-y-5 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800"
                >
                    <div>
                        <label htmlFor="workout_date" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Date
                        </label>
                        <input
                            id="workout_date"
                            type="date"
                            required
                            className="mt-1 block w-full rounded-md border-gray-300 px-3 py-2 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            value={data.workout_date}
                            onChange={(e) => setData('workout_date', e.target.value)}
                        />
                        {errors.workout_date && <p className="mt-1 text-sm text-red-600">{errors.workout_date}</p>}
                    </div>

                    <div>
                        <label htmlFor="notes" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Notes <span className="text-xs text-gray-500">(optional, max 500)</span>
                        </label>
                        <textarea
                            id="notes"
                            maxLength={500}
                            rows={3}
                            className="mt-1 block w-full rounded-md border-gray-300 px-3 py-2 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            value={data.notes}
                            onChange={(e) => setData('notes', e.target.value)}
                        />
                        {errors.notes && <p className="mt-1 text-sm text-red-600">{errors.notes}</p>}
                    </div>

                    <fieldset>
                        <legend className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Tag to groups
                        </legend>
                        <p className="mt-1 text-xs text-gray-500">
                            Tag the workout to every group it should count toward (at least one).
                        </p>
                        <div className="mt-2 space-y-2">
                            {availableGroups.map((group) => (
                                <label
                                    key={group.id}
                                    className="flex cursor-pointer items-center gap-2 rounded-md border border-gray-200 p-3 dark:border-gray-600"
                                >
                                    <input
                                        type="checkbox"
                                        checked={data.group_ids.includes(group.id)}
                                        onChange={() => toggleGroup(group.id)}
                                        className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <span className="text-sm text-gray-900 dark:text-gray-100">
                                        {group.name}
                                    </span>
                                </label>
                            ))}
                        </div>
                        {errors.group_ids && <p className="mt-1 text-sm text-red-600">{errors.group_ids}</p>}
                    </fieldset>

                    <div className="flex justify-end">
                        <button
                            type="submit"
                            disabled={processing}
                            className="inline-flex h-11 items-center justify-center rounded-md bg-indigo-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                        >
                            Create workout
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
