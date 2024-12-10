import {
    TextField
} from '@mui/material';
import React from 'react';

// TODO: Fix typing for setData and setError methods, consider adding style prop?
interface MetricInputProps {
    exerciseMetric: number | null;
    setData: (arg0: any, arg2: any) => void;
    setError: (arg0: any, arg2: any) => void;
    error: string | undefined;
    rest?: any;
};

const MetricInput: React.FC<MetricInputProps> = ({ exerciseMetric, setData, setError, error, ...rest }) => {

    const validateChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const regex = /^\d+$/;

        setError('exercise_metric', '');
        setData('exercise_metric', e.target.value);

        if (e.target.value.length === 0) return setError('exercise_metric', 'The exercise metric is a required field.');

        if (!regex.test(e.target.value)) return setError('exercise_metric', 'Exercise metric must be a number.');
    }

    return (
            <TextField
                id="metric"
                label="Metric"
                variant="outlined"
                required
                error={Boolean(error)}
                helperText={error}
                size='small'
                type='text'
                onChange={validateChange}
                value={exerciseMetric}
                {...rest}
            />
    );
}

export default MetricInput;
