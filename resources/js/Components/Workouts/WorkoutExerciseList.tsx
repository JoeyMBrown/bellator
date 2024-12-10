import { Link } from '@inertiajs/react';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import Paper from '@mui/material/Paper';
import { Icon, Stack } from '@mui/material';
import { Exercise } from '@/types';

interface ExerciseListType {
    workoutId: number,
    exercises: Array<null | Exercise>;
}

const ExerciseList: React.FC<ExerciseListType> = ({ workoutId, exercises }) => {

    return (
        <TableContainer component={Paper}>
            <Table sx={{ minWidth: 650 }} aria-label="simple table">
                <TableHead>
                    <TableRow>
                        <TableCell>Exercise</TableCell>
                        <TableCell align="right">Actions</TableCell>
                    </TableRow>
                </TableHead>
                <TableBody>
                {
                    exercises.map((exercise, index) => (
                        <TableRow
                            key={index}
                            sx={{ '&:last-child td, &:last-child th': { border: 0 } }}
                        >
                            <TableCell component="th" scope="row">
                                <Link href={ route('workout.exercise.show', {workout_id: workoutId, exercise_id: exercise?.pivot?.id}) }>
                                    {exercise?.name}
                                </Link>
                            </TableCell>
                            <TableCell align="right" component="th" scope="row">
                                <Stack justifyContent='flex-end' flexDirection='row'>
                                    <Icon sx={{ mx: 1, color: 'grey'}}>edit</Icon>
                                    <Icon sx={{ mx: 1, color: 'red'}}>delete</Icon>
                                </Stack>
                            </TableCell>
                        </TableRow>
                    ))
                }
                </TableBody>
            </Table>
        </TableContainer>
    );
}

export default ExerciseList;