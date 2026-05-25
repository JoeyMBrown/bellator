import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

const PRESETS = [
    {
        value: 'balanced',
        label: 'Balanced',
        description: 'Roughly equal weight across strength, cardio, and bodyweight work.',
    },
    {
        value: 'strength',
        label: 'Strength-focused',
        description: 'Weighted lifts score higher; cardio is still counted but worth less.',
    },
    {
        value: 'endurance',
        label: 'Endurance-focused',
        description: 'Distance work scores higher; lifts still count but worth less.',
    },
];

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        description: '',
        timezone:
            typeof Intl !== 'undefined'
                ? Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC'
                : 'UTC',
        preset_rubric: 'balanced',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(route('groups.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Create a group
                </h2>
            }
        >
            <Head title="Create a group" />

            <div className="mx-auto max-w-xl px-4 py-8 sm:px-6 lg:px-8">
                <form
                    onSubmit={submit}
                    className="space-y-5 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800"
                >
                    <div>
                        <label htmlFor="name" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Group name
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
                        <label htmlFor="timezone" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Timezone
                        </label>
                        <input
                            id="timezone"
                            type="text"
                            maxLength={64}
                            className="mt-1 block w-full rounded-md border-gray-300 px-3 py-2 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            value={data.timezone}
                            onChange={(e) => setData('timezone', e.target.value)}
                        />
                        {errors.timezone && <p className="mt-1 text-sm text-red-600">{errors.timezone}</p>}
                        <p className="mt-1 text-xs text-gray-500">
                            Used to compute the week-start cutoff on the leaderboard.
                        </p>
                    </div>

                    <fieldset>
                        <legend className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Starting rubric
                        </legend>
                        <p className="mt-1 text-xs text-gray-500">
                            Pre-populates point values for the seeded exercises. You can edit
                            everything in settings later.
                        </p>
                        <div className="mt-3 space-y-2">
                            {PRESETS.map((preset) => (
                                <label
                                    key={preset.value}
                                    className="flex cursor-pointer items-start gap-2 rounded-md border border-gray-200 p-3 hover:border-indigo-300 dark:border-gray-600"
                                >
                                    <input
                                        type="radio"
                                        name="preset_rubric"
                                        value={preset.value}
                                        checked={data.preset_rubric === preset.value}
                                        onChange={() => setData('preset_rubric', preset.value)}
                                        className="mt-1"
                                    />
                                    <span>
                                        <span className="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {preset.label}
                                        </span>
                                        <span className="block text-xs text-gray-600 dark:text-gray-400">
                                            {preset.description}
                                        </span>
                                    </span>
                                </label>
                            ))}
                        </div>
                        {errors.preset_rubric && (
                            <p className="mt-1 text-sm text-red-600">{errors.preset_rubric}</p>
                        )}
                    </fieldset>

                    <div className="flex items-center justify-between gap-3 pt-2">
                        <Link
                            href={route('onboarding')}
                            className="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="inline-flex h-11 items-center justify-center rounded-md bg-indigo-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                        >
                            Create group
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
