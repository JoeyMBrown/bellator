import {
    Autocomplete,
    Box,
    Button,
    Container,
    Paper,
    TextField
} from '@mui/material';

export default function CreateForm() {
    const top100Films: String[] = [
        "Anaerobic",
        "Aerobic",
        "Cardio"
    ]; // TODO: Values should be read from DB WorkoutTypes.

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
                <TextField
                    id="outlined-basic"
                    label="Name"
                    variant="outlined"
                    required
                    size="small"
                    fullWidth
                    sx={{ marginTop: 3 }}
                />

                <TextField
                    id="outlined-basic"
                    label="Description"
                    variant="outlined"
                    required
                    size="small"
                    fullWidth
                    sx={{ marginTop: 3 }}
                />

                <Autocomplete
                    disablePortal
                    options={top100Films}
                    sx={{ marginTop: 3 }}
                    renderInput={(params) => <TextField {...params} label="Workout Type" />}
                    size="small"
                    fullWidth
                />
            </Box>

            <Container className="mx-6 mt-12" style={{ display: 'flex', justifyContent: 'flex-end' }}>
                <Button variant="contained">Create Workout</Button>
            </Container>
        </Paper>
    );
}
