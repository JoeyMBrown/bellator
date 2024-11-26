import BreadcrumbNav from '@/Components/Workouts/BreadcrumbNav';
import WorkoutList from '@/Components/Workouts/WorkoutList';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Container } from '@mui/material';

interface Workout { // TODO: Move types to index.d.ts or global.d.ts
    id: number;
    workout_date: string;
    workout_type_id: null | number;
    user_id: string;
    created_at: null | string;
    updated_at: null | string;
    deleted_at: null | string;
}

interface WorkoutList {
    workouts: Array<null | Workout>;
}

const Create: React.FC<WorkoutList> = ({ workouts }) => {

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    My Workouts
                </h2>
            }
        >
            <Head title="My Workouts" />

            <Container>

                <BreadcrumbNav
                    currentPage='My Workouts'
                />
                
                <WorkoutList workouts={workouts} />
            </Container>

        </AuthenticatedLayout>
    );
}

export default Create;