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

export interface WorkoutExercise {
    id: number;
    workout_id: number;
    exercise_id: number;
    workout?: Workout;
    exercise?: Exercise;
    created_at: null | string;
    update_at: null | string;
    deleted_at: null | string;
    workout_exercise_logs?: Array<WorkoutExerciseLog | []>;
}

export interface MetricUnit {
    id: number;
    name: string;
    description: string;
    created_at: null | string;
    update_at: null | string;
    deleted_at: null | string;
}

export interface WorkoutExerciseLog {
    id: number;
    repitions: number;
    exercise_metric: number;
    exercise_points: number;
    workout_exercise_id: number;
    metric_unit_id: number;
    metric_unit?: MetricUnit;
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
