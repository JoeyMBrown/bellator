import BreadcrumbNav from '@/Components/Workouts/BreadcrumbNav';
import CreateForm from '@/Components/Workouts/CreateForm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Container } from '@mui/material';

export default function Create() {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Create Workout
                </h2>
            }
        >
            <Head title="Create Workout" />

            <Container>
                <BreadcrumbNav
                    currentPage='Create'
                />

                <CreateForm />
            </Container>

        </AuthenticatedLayout>
    );
}
