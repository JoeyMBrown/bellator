import { Link } from '@inertiajs/react';
import Paper from '@mui/material/Paper';
import {
    Card,
    CardContent,
    CardActions,
    Icon,
    Stack,
    Typography,
    Button
} from '@mui/material';
import { Exercise } from '@/types';

interface ExerciseListType {
    workoutId: number,
    exercises: Array<null | Exercise>;
}

const WorkoutExerciseCardList: React.FC<ExerciseListType> = ({ workoutId, exercises }) => {

    return (
        <Stack flexDirection='column' gap={2}>
            <Card>
                <CardContent>
                    <Typography>A list of all exercises performed in this workout session.</Typography>
                </CardContent>
            </Card>

                {

                    exercises.length === 0 ?
                    <>
                        <Card>
                            <CardContent>
                                <Typography>Looks like you haven't created any exercises yet. Click the button below to create your first exercise.</Typography>
                            </CardContent>
                            
                            <CardActions sx={{ display: 'flex', justifyContent: 'flex-end' }}>
                                <Link href={route('workout.exercise.create', workoutId)}>
                                    <Button color="primary" variant="contained">Add Exercise</Button>
                                </Link>
                            </CardActions>
                        </Card>
                    </>
                    :
                    exercises.map((exercise, index) => (
                        <Card
                            key={index}
                            className='pt-2'
                        >
                            <CardContent component="th" scope="row">
                                <Link href={ route('workout.exercise.show', {workout_id: workoutId, exercise_id: exercise?.pivot?.id}) }>
                                    {exercise?.name}
                                </Link>
                            </CardContent>
                            <CardActions>
                                <Stack justifyContent='flex-end' flexDirection='row'>
                                    <Icon sx={{ mx: 1, color: 'grey'}}>edit</Icon>
                                    <Icon sx={{ mx: 1, color: 'red'}}>delete</Icon>
                                </Stack>
                            </CardActions>
                        </Card>
                    ))
                }
        </Stack>
    );
}

export default WorkoutExerciseCardList;