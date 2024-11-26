import { Link } from '@inertiajs/react';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import Paper from '@mui/material/Paper';
import { Icon } from '@mui/material';

interface Workout {// TODO: Move types to index.d.ts or global.d.ts - ask ChatGPT
    id: number;
    workout_date: string;
    workout_type_id: null | number;
    user_id: string;
    created_at: null | string;
    updated_at: null | string;
    deleted_at: null | string;
}

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
                            key={workout.id}
                            sx={{ '&:last-child td, &:last-child th': { border: 0 } }}
                        >
                            <Link href={route('workout.show', workout.id)}>
                                <TableCell component="th" scope="row">
                                {workout.workout_date}
                                </TableCell>
                            </Link>
                            <TableCell align="right" component="th" scope="row">
                                <Icon sx={{ color: 'red'}}>delete</Icon>
                            </TableCell>
                        </TableRow>
                    ))
                }
                </TableBody>
            </Table>
        </TableContainer>
    );
}

export default WorkoutList;