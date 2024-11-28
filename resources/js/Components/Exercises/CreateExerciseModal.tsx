import {
    Box,
    Button,
    IconButton,
    Modal,
    Paper,
    Stack,
    Typography
} from '@mui/material';
import CreateExerciseForm from './CreateExerciseForm';
import { Option } from '@/types';
import { useForm } from '@inertiajs/react';

interface ModalProps {
    open: boolean;
    handleClose: () => void;
    exerciseOptions: Array<Option | []>
};

const style: React.CSSProperties = {
    position: 'absolute',
    top: '50%',
    left: '50%',
    transform: 'translate(-50%, -50%)',
    width: 400,
    p: 2
};


const CreateExerciseModal: React.FC<ModalProps> = ({ open, handleClose, exerciseOptions, workoutId }) => {
    const { data, setData, post, processing, errors } = useForm<Option | null>(null);

    const handleSubmit = () => {
        post( route('workout.exercise.store', { id: workoutId }) );
        // post to /workout/{id}/exercise
        // TODO: Post form data
        // TODO: Close Modal
    }

    return (
        <Modal
            open={open}
            onClose={handleClose}
            aria-labelledby="create-exercise-modal"
            aria-describedby="create-modal-description"
        >
            <Paper sx={style}>
                <Stack
                    flexDirection='row'
                    justifyContent='space-between'
                >
                    <Stack justifyContent='center'>
                        <Typography
                            id="create-exercise-modal"
                            variant="h6"
                            component="h2"
                            alignSelf='flex-start'
                        >
                            Add Exercise to Workout
                        </Typography>
                    </Stack>

                    <IconButton onClick={handleClose}>
                        <span className="material-icons">close</span>
                    </IconButton>
                </Stack>

                <Stack sx={{ p: 2 }}>
                    <CreateExerciseForm exerciseOptions={exerciseOptions} data={data} setData={setData} />
                </Stack>

                <Stack flexDirection='row' justifyContent='flex-end'>
                    <Box sx={{ mx: 1 }}>
                        <Button onClick={handleSubmit} variant='outlined'>Close</Button>
                    </Box>

                    <Box sx={{ mx: 1 }}>
                        <Button onClick={handleSubmit} variant='contained'>Submit</Button>
                    </Box>
                </Stack>
            </Paper>
        </Modal>
    );
}

export default CreateExerciseModal;