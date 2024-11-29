export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export interface Workout {
    id: number;
    workout_date: string;
    workout_type_id: null | number;
    user_id: string;
    exercises?: Array<Exercise | []>;
    created_at: null | string;
    updated_at: null | string;
    deleted_at: null | string;
}

export interface Exercise {
    id: number;
    name: string;
    description: string;
    created_at: null | string;
    updated_at: null | string;
    deleted_at: null | string;
}

export interface WorkoutList {
    workouts: Array<null | Workout>;
}

export interface Option {
    id: number,
    name: string
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
