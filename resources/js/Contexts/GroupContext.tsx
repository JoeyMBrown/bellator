import { usePage } from '@inertiajs/react';
import {
    createContext,
    PropsWithChildren,
    useCallback,
    useContext,
    useEffect,
    useMemo,
    useState,
} from 'react';
import { PageProps, UserGroupSummary } from '@/types';

const LAST_VIEWED_GROUP_KEY = 'bellator.lastViewedGroupId';

interface GroupContextValue {
    groups: UserGroupSummary[];
    activeGroupId: number | null;
    setActiveGroupId: (id: number | null) => void;
    activeGroup: UserGroupSummary | null;
}

const GroupContext = createContext<GroupContextValue | null>(null);

export const GroupContextProvider = ({ children }: PropsWithChildren) => {
    const { props } = usePage<PageProps>();
    const groups = props.userGroups ?? [];

    const initialActiveGroupId = (() => {
        const fromUrl = readGroupIdFromUrl();
        if (fromUrl !== null && groups.some((g) => g.id === fromUrl)) return fromUrl;

        if (typeof window !== 'undefined') {
            const stored = window.localStorage.getItem(LAST_VIEWED_GROUP_KEY);
            const parsed = stored ? parseInt(stored, 10) : NaN;
            if (!Number.isNaN(parsed) && groups.some((g) => g.id === parsed)) {
                return parsed;
            }
        }

        return groups[0]?.id ?? null;
    })();

    const [activeGroupId, setActiveGroupIdState] =
        useState<number | null>(initialActiveGroupId);

    const setActiveGroupId = useCallback((id: number | null) => {
        setActiveGroupIdState(id);
        if (typeof window === 'undefined') return;
        if (id === null) {
            window.localStorage.removeItem(LAST_VIEWED_GROUP_KEY);
        } else {
            window.localStorage.setItem(LAST_VIEWED_GROUP_KEY, String(id));
        }
    }, []);

    useEffect(() => {
        const fromUrl = readGroupIdFromUrl();
        if (fromUrl !== null && groups.some((g) => g.id === fromUrl)) {
            setActiveGroupId(fromUrl);
        }
    }, [groups, setActiveGroupId]);

    const value = useMemo<GroupContextValue>(
        () => ({
            groups,
            activeGroupId,
            setActiveGroupId,
            activeGroup:
                groups.find((g) => g.id === activeGroupId) ?? null,
        }),
        [groups, activeGroupId, setActiveGroupId],
    );

    return (
        <GroupContext.Provider value={value}>{children}</GroupContext.Provider>
    );
};

export const useGroupContext = () => {
    const ctx = useContext(GroupContext);
    if (ctx === null) {
        throw new Error('useGroupContext must be used within GroupContextProvider');
    }
    return ctx;
};

function readGroupIdFromUrl(): number | null {
    if (typeof window === 'undefined') return null;
    const match = window.location.pathname.match(/^\/groups\/(\d+)/);
    if (match === null) return null;
    const parsed = parseInt(match[1], 10);
    return Number.isNaN(parsed) ? null : parsed;
}
