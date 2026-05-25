import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

export default function GroupGate() {
    const { data, setData, post, processing, errors, reset } = useForm({
        invite_code: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(route('groups.join'), {
            onSuccess: () => reset('invite_code'),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Welcome to Bellator
                </h2>
            }
        >
            <Head title="Get started" />

            <div className="mx-auto max-w-2xl px-4 py-10 sm:px-6 lg:px-8">
                <p className="text-base text-gray-700 dark:text-gray-300">
                    To get started, join an existing group with an invite code,
                    or create your own group and invite your friends.
                </p>

                <div className="mt-8 grid gap-6 sm:grid-cols-2">
                    <section className="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Join a group
                        </h3>
                        <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Enter the invite code a friend sent you.
                        </p>
                        <form onSubmit={submit} className="mt-4 space-y-3">
                            <label
                                htmlFor="invite_code"
                                className="block text-sm font-medium text-gray-700 dark:text-gray-200"
                            >
                                Invite code
                            </label>
                            <input
                                id="invite_code"
                                type="text"
                                autoCapitalize="characters"
                                autoCorrect="off"
                                spellCheck={false}
                                placeholder="BELL-XXXXXXXX"
                                className="block w-full rounded-md border-gray-300 px-3 py-2 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                value={data.invite_code}
                                onChange={(e) =>
                                    setData('invite_code', e.target.value)
                                }
                            />
                            {errors.invite_code && (
                                <p className="text-sm text-red-600">
                                    {errors.invite_code}
                                </p>
                            )}
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex h-11 w-full items-center justify-center rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 sm:w-auto"
                            >
                                Join group
                            </button>
                        </form>
                    </section>

                    <section className="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Create a group
                        </h3>
                        <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Start a new group and invite your friends.
                        </p>
                        <Link
                            href={route('groups.create')}
                            className="mt-4 inline-flex h-11 w-full items-center justify-center rounded-md border border-indigo-600 px-4 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:border-indigo-400 dark:text-indigo-300 dark:hover:bg-indigo-900/30 sm:w-auto"
                        >
                            Create new group
                        </Link>
                    </section>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
