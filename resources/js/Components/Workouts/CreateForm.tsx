import { useForm } from '@inertiajs/react'
import {
    Autocomplete,
    Box,
    Button,
    Container,
    Paper,
    TextField
} from '@mui/material';

export default function CreateForm() {
    type workoutTypeOption = {
        displayText: string;
        id: number;
    }

    interface formData {
        name: string,
        description: string,
        workout_type_id: number | null,
    }

    const workoutTypeOptions: workoutTypeOption[] = [
        { 
            displayText: "Anaerobic",
            id: 1
        },
        { 
            displayText: "Aerobic",
            id: 2
        },
        { 
            displayText: "Cardio",
            id: 3
        }
    ]; // TODO: Values should be read from DB WorkoutTypes.

    //TODO: Determine consistent Max Width / Min Width setting for form

    const handleWorkoutTypeChange = (event: any, value: workoutTypeOption | null) => {
        setData('workout_type_id', value?.id || null);
    };

    const { data, setData, post, processing, errors } = useForm<formData>({
        name: '',
        description: '',
        workout_type_id: 1,
    })

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('workout.store'));
    }

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
                <TextField
                    value={data.name}
                    onChange={e => setData('name', e.target.value)}
                    id="outlined-basic"
                    label="Name"
                    variant="outlined"
                    required
                    size="small"
                    fullWidth
                    sx={{ marginTop: 3 }}
                />

                <TextField
                    value={data.description}
                    onChange={e => setData('description', e.target.value)}
                    id="outlined-basic"
                    label="Description"
                    variant="outlined"
                    required
                    size="small"
                    fullWidth
                    sx={{ marginTop: 3 }}
                />

                <Autocomplete
                    onChange={handleWorkoutTypeChange}
                    disablePortal
                    options={workoutTypeOptions}
                    getOptionLabel={(option) => option.displayText}
                    sx={{ marginTop: 3 }}
                    renderInput={(params) => <TextField {...params} label="Workout Type" />}
                    size="small"
                    fullWidth
                />
            </Box>

            <Container className="mx-6 mt-12" style={{ display: 'flex', justifyContent: 'flex-end' }}>
                <Button variant="contained" type="submit" onClick={handleSubmit}>Create Workout</Button>
            </Container>
        </Paper>
    );
}
