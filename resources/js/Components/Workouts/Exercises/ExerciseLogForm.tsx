import {
    Box,
    Paper,
    Stack,
    Typography
} from '@mui/material';
import MetricUnitSelector from './MetricUnitSelector';
import ExerciseLogFormRow from './ExerciseLogFormRow';
import { useForm } from '@inertiajs/react';
import { MetricUnit, WorkoutExerciseLog } from '@/types';

interface ExerciseLogFormProps {
    metricUnitOptions: Array<MetricUnit | []>;
    workoutExerciseLogs: Array<WorkoutExerciseLog | [] >; //TODO: Typing on this is likely wrong.
    workoutId: number;
    workoutExerciseId: number;
};

const ExerciseLogForm: React.FC<ExerciseLogFormProps> = ({ metricUnitOptions, workoutExerciseLogs, workoutId, workoutExerciseId }) => {
    // TODO:
    // 1. Define a FormInputBox component that will be reused for different key points here.
    // 2. Implement specific components, repitionInput etc (that handle their own validation?)
    // 3. Define a ExerciseLogFormLineItem component or something similar that will hold form state
    // 3a. for all individual inputs.  They will need to be posted at same time so this should make sense.

    interface formData {
        selected_exercise_metric_unit: MetricUnit | null,
    }

    // TODO: Prefill this with Exercise Metric for current Workout Exercise from DB.
    const { data, setData, setError } = useForm<formData>({
        selected_exercise_metric_unit: null
    });

    //TODO: Determine consistent Max Width / Min Width setting for form

    return (
        <Paper className="mt-12 mx-auto py-6" style={{ maxWidth: '800px', minWidth: '400px' }}>
            <Box
                className="mx-auto"
                style={{
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: 'center',
                    width: '80%'
                }}
            >
                <Box>
                    <Stack direction="row" justifyContent='space-around' sx={{ my: 4 }}>
                        <Stack justifyContent='center' alignContent='center'>
                            <Typography>Repitions</Typography>
                        </Stack>
                        
                        <Box>
                            <MetricUnitSelector
                                metricUnitOptions={metricUnitOptions}
                                selectedMetricUnit={data.selected_exercise_metric_unit}
                                setData={setData}
                                setError={setError}
                            />
                        </Box>
                    </Stack>

                    {/**
                     * This form row will always exist as a new empty form that allows user to
                     * create a new exercise log.
                     */}
                    
                    <ExerciseLogFormRow
                        exerciseLog={{}}
                        metricUnitId={data.selected_exercise_metric_unit?.id}
                        workoutId={workoutId}
                        workoutExerciseId={workoutExerciseId}
                    />

                    {
                        /** TODO: Each group of inputs will need to be it's own form component
                         *  this will allow for rendering of multiple components where each
                         *  component or group of inputs has it's pre-filled values from the
                         *  DB or will allow for a blank form state.  This is the only way
                         *  editing will work as well.
                         */

                        workoutExerciseLogs.map((exerciseLog) => {
                            return (
                                <ExerciseLogFormRow
                                    key={exerciseLog.id}
                                    exerciseLog={exerciseLog}
                                    metricUnitId={data.selected_exercise_metric_unit?.id}
                                    workoutId={workoutId}
                                    workoutExerciseId={workoutExerciseId}
                                />
                            )
                        })

                    }
                </Box>

            </Box>

        </Paper>
    );
}

export default ExerciseLogForm;
