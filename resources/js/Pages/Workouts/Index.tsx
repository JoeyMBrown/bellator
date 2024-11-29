import WorkoutlistComponent  from '@/Components/Workouts/WorkoutList';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Breadcrumbs, Container, Typography } from '@mui/material';
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

                <Breadcrumbs aria-label="breadcrumb" sx={{ my: '3rem' }}>
                    <Link href={route('dashboard')}>
                        Dashboard
                    </Link>

                    <Typography sx={{ color: 'text.primary' }}>Workouts</Typography>
                </Breadcrumbs>
                
                <WorkoutlistComponent workouts={workouts} />
            </Container>

        </AuthenticatedLayout>
    );
}

export default Create;