import { useForm } from '@inertiajs/react';
import {
    Box,
    Button,
    Container,
    Paper
} from '@mui/material';
import RepitionInput from './RepitionInput';

export default function ExerciseLogForm() {
    // TODO:
    // 1. Define a FormInputBox component that will be reused for different key points here.
    // 2. Implement specific components, repitionInput etc (that handle their own validation?)
    // 3. Define a ExerciseLogFormLineItem component or something similar that will hold form state
    // 3a. for all individual inputs.  They will need to be posted at same time so this should make sense.

    interface formData {
        repitions: number | undefined,
        workout_date: string | null,
        workout_type_id: number | null,
    }

    const { data, setData, post, processing, setError, errors, clearErrors } = useForm({
        repitions: 0,
        remember: false,
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

                <RepitionInput
                    repitions={data.repitions}
                    setData={setData}
                    setError={setError}
                    error={errors.repitions}
                />

            </Box>

            <Container className="mx-6 mt-12" style={{ display: 'flex', justifyContent: 'flex-end' }}>
                <Button variant="contained" type="submit" disabled={processing}>Create Workout</Button>
            </Container>
        </Paper>
    );
}
