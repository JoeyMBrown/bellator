import { Link } from '@inertiajs/react';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import Paper from '@mui/material/Paper';
import { Button, Icon, Typography } from '@mui/material';
import { Workout } from '@/types';

interface WorkoutList {
    workouts: Array<null | Workout>;
}

const WorkoutList: React.FC<WorkoutList> = ({ workouts }) => {
    return (
        <TableContainer component={Paper}>
            <Table sx={{ minWidth: 650 }} aria-label="simple table">
                <TableHead>
                    <TableRow>
                        <TableCell>Workout Date</TableCell>
                        <TableCell align="right">Actions</TableCell>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {
                        workouts.map((workout) => (
                            <TableRow
                                key={workout?.id}
                                sx={{ '&:last-child td, &:last-child th': { border: 0 } }}
                            >
                                <TableCell component="th" scope="row">
                                    <Link href={route('workout.show', workout?.id)}>
                                        {workout?.workout_date}
                                    </Link>
                                </TableCell>
                                <TableCell align="right" component="th" scope="row">
                                    <Icon sx={{ color: 'red'}}>delete</Icon>
                                </TableCell>
                            </TableRow>
                        ))
                    }

                    {workouts.length === 0 && (
                        <>
                            <Typography>Looks like you haven't created any workouts yet. Click the button below to create your first workout.</Typography>

                            <Link href={route('workout.create')}>
                                <Button variant="contained" color="primary">
                                    Create Workout
                                </Button>
                            </Link>
                        </>
                    )}
                    
                </TableBody>
            </Table>
        </TableContainer>
    );
}

export default WorkoutList;