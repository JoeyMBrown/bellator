export interface User {
    id: string;
    name: string;
    email: string;
    email_verified_at?: string;
    timezone?: string;
}

export type GroupRole = 'owner' | 'admin' | 'member';

export type MeasurementType =
    | 'reps_only'
    | 'weighted_reps'
    | 'distance'
    | 'duration';

export interface UserGroupSummary {
    id: number;
    name: string;
    role: GroupRole;
}

export interface GroupMemberSummary {
    id: number;
    user_id: string;
    name: string;
    email: string | null;
    role: GroupRole;
    joined_at: string;
}

export interface Group {
    id: number;
    name: string;
    description: string | null;
    invite_code: string;
    timezone: string;
    created_at: string;
    role: GroupRole | null;
    members: GroupMemberSummary[];
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    flash: {
        success: string | null;
        error: string | null;
    };
    userGroups: UserGroupSummary[];
};
