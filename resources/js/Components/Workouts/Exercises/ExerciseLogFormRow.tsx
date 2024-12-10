import { useForm } from '@inertiajs/react';
import { IconButton, Stack } from "@mui/material";
import RepitionInput from "./RepitionInput";
import MetricInput from "./MetricInput";
import { useEffect } from 'react';
import { WorkoutExerciseLog } from '@/types';

interface ExerciseLogFormRowProps {
    exerciseLog: WorkoutExerciseLog;
    metricUnitId: number;
    workoutId: number;
    workoutExerciseId: number;
};

const ExerciseLogFormRow: React.FC<ExerciseLogFormRowProps> = ({ exerciseLog, metricUnitId, workoutId, workoutExerciseId }) => {

    useEffect(() => {
        setData('metric_unit_id', metricUnitId);
    }, [metricUnitId])

    const { data, setData, post, processing, setError, errors, clearErrors } = useForm({
        repitions: 0,
        exercise_metric: 0,
        metric_unit_id: metricUnitId
    });

    const handleSubmit = () => {
        // TODO: Validate form is valid before post
        post( route('workout.exercise.log.store', { workout_id: workoutId, exercise_id: workoutExerciseId }) );
    }

    return (
        <Stack flexDirection='row' justifyContent='space-between'>
            <RepitionInput
                repitions={exerciseLog?.repitions || data.repitions}
                setData={setData}
                setError={setError}
                error={errors.repitions}
                sx={{
                    px: 2
                }}
            />

            <MetricInput
                exerciseMetric={exerciseLog?.exercise_metric || data.exercise_metric}
                setData={setData}
                setError={setError}
                error={errors.exercise_metric}
                sx={{
                    px: 2
                }}
            />

            <IconButton
                disabled={processing}
                onClick={handleSubmit}
            ><span className="material-icons">send</span>
            </IconButton>
        </Stack>
    );
}

export default ExerciseLogFormRow;
