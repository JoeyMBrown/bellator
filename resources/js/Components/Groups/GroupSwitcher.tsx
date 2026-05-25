import { router } from '@inertiajs/react';
import { useGroupContext } from '@/Contexts/GroupContext';
import { ChangeEvent } from 'react';

const GroupSwitcher = () => {
    const { groups, activeGroupId, setActiveGroupId } = useGroupContext();

    if (groups.length === 0) return null;

    const handleChange = (e: ChangeEvent<HTMLSelectElement>) => {
        const id = parseInt(e.target.value, 10);
        if (Number.isNaN(id)) return;
        setActiveGroupId(id);
        router.visit(route('groups.show', id));
    };

    return (
        <select
            value={activeGroupId ?? ''}
            onChange={handleChange}
            aria-label="Switch group"
            className="rounded-md border-gray-300 bg-white py-1.5 pl-3 pr-8 text-sm font-medium text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
        >
            {groups.map((group) => (
                <option key={group.id} value={group.id}>
                    {group.name}
                </option>
            ))}
        </select>
    );
};

export default GroupSwitcher;
