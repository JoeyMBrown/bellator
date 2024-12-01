import { Link } from '@inertiajs/react';
import {
    Breadcrumbs,
    Typography
} from '@mui/material';

interface BreadcrumbNav {
    currentPage: string;
}

const BreadcrumbNav: React.FC<BreadcrumbNav> = ({ currentPage, workoutId, workoutDate }) => {
    return (
        <Breadcrumbs aria-label="breadcrumb" sx={{ my: '3rem' }}>
                <Link href={route('dashboard')}>
                    Dashboard
                </Link>

                <Link href={route('workout.index')}>
                    Workouts
                </Link>

                <Link href={route('workout.show', workoutId)}>
                    { workoutDate }
                </Link>

            <Typography sx={{ color: 'text.primary' }}>{currentPage}</Typography>
        </Breadcrumbs>
    );
}

export default BreadcrumbNav;