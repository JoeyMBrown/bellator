import { Link } from '@inertiajs/react';
import {
    Breadcrumbs,
    Typography
} from '@mui/material';

export default function BreadcrumbNav() {
    return (
        <Breadcrumbs aria-label="breadcrumb">
                <Link href={route('dashboard')}>
                    Dashboard
                </Link>

                <Link href={route('workout.create')}> {/** TODO: Change to workout.index when route is defined */}
                    Workouts
                </Link>
            <Typography sx={{ color: 'text.primary' }}>Create</Typography>
        </Breadcrumbs>
    );
}