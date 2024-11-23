import { useForm } from '@inertiajs/react';
import dayjs, { Dayjs as DayjsType } from 'dayjs';
import {
    Autocomplete,
    Box,
    Button,
    Container,
    Paper,
    TextField
} from '@mui/material';
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs';
import { DatePicker, LocalizationProvider } from '@mui/x-date-pickers';

export default function CreateForm() {
    type workoutTypeOption = {
        displayText: string;
        id: number;
    }

    interface formData {
        workout_date_selection: DayjsType,
        workout_date: string | null,
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

    const handleWorkoutDateChange = (event: any, value: DayjsType) => {
        setData({
            ...data,
            'workout_date': value.format('YYYY-MM-DD HH:mm:ss'), //TODO: This will need stored in UTC format to avoid confusion.  Need cast on backend.
            'workout_date_selection': value
        });
    };

    const { data, setData, post, processing, errors, transform } = useForm<formData>({
        workout_date_selection: dayjs(),
        workout_date: dayjs().format('YYYY-MM-DD HH:mm:ss'),
        workout_type_id: null,
    })

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        // TODO: Validation

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

                <LocalizationProvider dateAdapter={AdapterDayjs}>
                    <DatePicker
                        label="Date of Workout"
                        value={data.workout_date_selection}
                        onChange={handleWorkoutDateChange}
                    />
                </LocalizationProvider>

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
                <Button variant="contained" type="submit" disabled={processing} onClick={handleSubmit}>Create Workout</Button>
            </Container>
        </Paper>
    );
}
