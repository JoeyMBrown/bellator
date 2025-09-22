import BreadcrumbNav from '@/Components/Workouts/BreadcrumbNav';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Container, Paper } from '@mui/material';
import { Workout, Option } from '@/types';
import FAB from '@/Components/General/FAB';
import { useState } from 'react';
import CreateExerciseModal from '@/Components/Exercises/CreateExerciseModal';
import WorkoutExerciseCardList from '@/Components/Workouts/WorkoutExerciseCardList';


interface ShowProps {
    workout: Workout
    exerciseOptions: Array<Option | []>;
};

const Show: React.FC<ShowProps> = ({ workout, exerciseOptions }) => {

    const [showCreateExerciseModal, setShowCreateExerciseModal] = useState<boolean>(false);

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Workout
                </h2>
            }
        >
            <Head title="Workout" />

            <Container>

                <BreadcrumbNav
                    currentPage={workout.workout_date}
                />

                    <WorkoutExerciseCardList workoutId={workout.id} exercises={workout.exercises}/>
                <Paper>
                </Paper>

                <FAB handleClick={() => setShowCreateExerciseModal(true)} />
                {/**
                 * TODO: Import modal
                 * Make modal pop on FAB click
                 * Add exercise creation form to modal
                 * List all exercises in workout including after new exercise creation
                 */}
                
            </Container>

            <CreateExerciseModal
                open={showCreateExerciseModal}
                handleClose={() => setShowCreateExerciseModal(false)}
                exerciseOptions={exerciseOptions}
                workoutId={workout.id}
            />

        </AuthenticatedLayout>
    );
}

export default Show;