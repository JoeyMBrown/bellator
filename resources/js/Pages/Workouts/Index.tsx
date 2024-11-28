import BreadcrumbNav from '@/Components/Workouts/BreadcrumbNav';
import WorkoutlistComponent  from '@/Components/Workouts/WorkoutList';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Container } from '@mui/material';
import { WorkoutList } from '@/types';

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
                
                <WorkoutlistComponent workouts={workouts} />
            </Container>

        </AuthenticatedLayout>
    );
}

export default Create;