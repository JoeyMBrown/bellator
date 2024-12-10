import BreadcrumbNav from '@/Components/Workouts/Exercises/BreadcrumbNav';
import ExerciseLogForm from '@/Components/Workouts/Exercises/ExerciseLogForm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { MetricUnit, WorkoutExercise } from '@/types';
import { Head } from '@inertiajs/react';
import { Container } from '@mui/material';

interface ShowProps {
    workoutExercise: WorkoutExercise;
    metricUnitOptions: Array<MetricUnit | []>;
}; // TODO: Define page props.

const Show: React.FC<ShowProps> = ({ workoutExercise, metricUnitOptions }) => {

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    { workoutExercise.exercise?.name }
                </h2>
            }
        >
            <Head title={workoutExercise.exercise?.name} />

            <Container>
                <BreadcrumbNav
                    currentPage={workoutExercise.exercise?.name}
                    workoutId={workoutExercise?.workout?.id}
                    workoutDate={workoutExercise?.workout?.workout_date}
                />

                <ExerciseLogForm
                    metricUnitOptions={metricUnitOptions}
                    workoutExerciseLogs={workoutExercise.workout_exercise_logs}
                    workoutExerciseId={workoutExercise.id}
                    workoutId={workoutExercise?.workout?.id}
                />

            </Container>

        </AuthenticatedLayout>
    );
}

export default Show;
