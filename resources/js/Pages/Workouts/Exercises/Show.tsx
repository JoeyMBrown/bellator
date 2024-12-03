import BreadcrumbNav from '@/Components/Workouts/Exercises/BreadcrumbNav';
import ExerciseLogForm from '@/Components/Workouts/Exercises/ExerciseLogForm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { WorkoutExercise } from '@/types';
import { Head } from '@inertiajs/react';
import { Container } from '@mui/material';

interface ShowProps {};

export default function Show({workoutExercises}) {

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    { workoutExercises.exercise?.name }
                </h2>
            }
        >
            <Head title={workoutExercises.exercise?.name} />

            <Container>
                <BreadcrumbNav
                    currentPage={workoutExercises.exercise?.name}
                    workoutId={workoutExercises.workout.id}
                    workoutDate={workoutExercises.workout.workout_date}
                />

                <ExerciseLogForm />

                { /** foreach exerciselog, generate a line item */}
                { /** First input should be the repitions */}
                { /** Second input should be the exercise_metric */}
                { /** Third input should be the metric unit */}

                {/** Exercise set logging form. */}

            </Container>

        </AuthenticatedLayout>
    );
}
