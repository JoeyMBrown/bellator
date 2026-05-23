import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { FormEvent } from 'react';
import { Group, GroupMemberSummary, PageProps } from '@/types';

interface EditProps {
    group: Group;
}

export default function Edit({ group }: EditProps) {
    const { props } = usePage<PageProps>();
    const currentUserId = props.auth.user?.id;

    const isOwner = group.role === 'owner';

    const { data, setData, put, processing, errors } = useForm({
        name: group.name,
        description: group.description ?? '',
        timezone: group.timezone,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put(route('groups.update', group.id), { preserveScroll: true });
    };

    const regenerate = () => {
        if (
            !window.confirm(
                'Regenerating will invalidate the old invite code. Continue?',
            )
        ) {
            return;
        }
        router.post(
            route('groups.invite-code.regenerate', group.id),
            {},
            { preserveScroll: true },
        );
    };

    const removeMember = (member: GroupMemberSummary) => {
        if (!window.confirm(`Remove ${member.name} from the group?`)) return;
        router.delete(route('groups.members.destroy', [group.id, member.user_id]), {
            preserveScroll: true,
        });
    };

    const updateRole = (member: GroupMemberSummary, role: 'admin' | 'member') => {
        router.patch(
            route('groups.members.role', [group.id, member.user_id]),
            { role },
            { preserveScroll: true },
        );
    };

    const leave = () => {
        if (!window.confirm('Leave this group?')) return;
        router.delete(route('groups.leave', group.id));
    };

    const destroyGroup = () => {
        if (
            !window.confirm(
                'Delete this group? Members will lose access. This cannot be undone.',
            )
        ) {
            return;
        }
        router.delete(route('groups.destroy', group.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between gap-3">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        {group.name} — Settings
                    </h2>
                    <Link
                        href={route('groups.show', group.id)}
                        className="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                    >
                        Back to group
                    </Link>
                </div>
            }
        >
            <Head title={`${group.name} — Settings`} />

            <div className="mx-auto max-w-3xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <form
                    onSubmit={submit}
                    className="space-y-4 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800"
                >
                    <h3 className="text-base font-semibold text-gray-900 dark:text-gray-100">
                        Group details
                    </h3>
                    <div>
                        <label
                            htmlFor="name"
                            className="block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >
                            Name
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
                        {errors.name && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.name}
                            </p>
                        )}
                    </div>
                    <div>
                        <label
                            htmlFor="description"
                            className="block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >
                            Description
                        </label>
                        <input
                            id="description"
                            type="text"
                            maxLength={140}
                            className="mt-1 block w-full rounded-md border-gray-300 px-3 py-2 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            value={data.description}
                            onChange={(e) =>
                                setData('description', e.target.value)
                            }
                        />
                        {errors.description && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.description}
                            </p>
                        )}
                    </div>
                    <div>
                        <label
                            htmlFor="timezone"
                            className="block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >
                            Timezone
                        </label>
                        <input
                            id="timezone"
                            type="text"
                            maxLength={64}
                            className="mt-1 block w-full rounded-md border-gray-300 px-3 py-2 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            value={data.timezone}
                            onChange={(e) =>
                                setData('timezone', e.target.value)
                            }
                        />
                        {errors.timezone && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.timezone}
                            </p>
                        )}
                    </div>
                    <button
                        type="submit"
                        disabled={processing}
                        className="inline-flex h-11 items-center justify-center rounded-md bg-indigo-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                    >
                        Save changes
                    </button>
                </form>

                <section className="space-y-3 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3 className="text-base font-semibold text-gray-900 dark:text-gray-100">
                        Invite code
                    </h3>
                    <div className="flex items-center justify-between gap-3">
                        <code className="rounded bg-gray-100 px-2 py-1 font-mono text-base text-gray-900 dark:bg-gray-700 dark:text-gray-100">
                            {group.invite_code}
                        </code>
                        <button
                            type="button"
                            onClick={regenerate}
                            className="rounded-md bg-amber-50 px-3 py-2 text-sm font-medium text-amber-700 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-300"
                        >
                            Regenerate
                        </button>
                    </div>
                </section>

                <section className="space-y-3 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3 className="text-base font-semibold text-gray-900 dark:text-gray-100">
                        Members
                    </h3>
                    <ul className="divide-y divide-gray-100 dark:divide-gray-700">
                        {group.members.map((member) => (
                            <li
                                key={member.id}
                                className="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div>
                                    <div className="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {member.name}
                                    </div>
                                    <div className="text-xs uppercase text-gray-500">
                                        {member.role}
                                    </div>
                                </div>
                                {member.user_id !== currentUserId &&
                                    member.role !== 'owner' && (
                                        <div className="flex gap-2">
                                            {isOwner && member.role === 'member' && (
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        updateRole(
                                                            member,
                                                            'admin',
                                                        )
                                                    }
                                                    className="rounded-md bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-100"
                                                >
                                                    Promote
                                                </button>
                                            )}
                                            {isOwner && member.role === 'admin' && (
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        updateRole(
                                                            member,
                                                            'member',
                                                        )
                                                    }
                                                    className="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200"
                                                >
                                                    Demote
                                                </button>
                                            )}
                                            <button
                                                type="button"
                                                onClick={() => removeMember(member)}
                                                className="rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100"
                                            >
                                                Remove
                                            </button>
                                        </div>
                                    )}
                            </li>
                        ))}
                    </ul>
                </section>

                <section className="space-y-3 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3 className="text-base font-semibold text-gray-900 dark:text-gray-100">
                        Library
                    </h3>
                    <div className="flex flex-wrap gap-2">
                        <Link
                            href={route('groups.exercises.index', group.id)}
                            className="rounded-md bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100"
                        >
                            Manage exercises
                        </Link>
                        <Link
                            href={route('groups.rubric.index', group.id)}
                            className="rounded-md bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100"
                        >
                            Manage rubric
                        </Link>
                    </div>
                </section>

                <section className="space-y-3 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3 className="text-base font-semibold text-gray-900 dark:text-gray-100">
                        Danger zone
                    </h3>
                    {!isOwner && (
                        <button
                            type="button"
                            onClick={leave}
                            className="rounded-md bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100"
                        >
                            Leave group
                        </button>
                    )}
                    {isOwner && (
                        <button
                            type="button"
                            onClick={destroyGroup}
                            className="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500"
                        >
                            Delete group
                        </button>
                    )}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
