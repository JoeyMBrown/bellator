import {
    TextField
} from '@mui/material';
import React from 'react';

// TODO: Fix typing for setData and setError methods, consider adding style prop?
interface RepetitionInputProps {
    repetitions: number | null;
    setData: (arg0: any, arg2: any) => void;
    setError: (arg0: any, arg2: any) => void;
    error: string | undefined;
    rest?: any;
};

const RepetitionInput: React.FC<RepetitionInputProps> = ({ repetitions, setData, setError, error, ...rest }) => {

    const validateChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const regex = /^\d+$/;

        setError('repetitions', '');
        setData('repetitions', e.target.value);

        if (e.target.value.length === 0) return setError('repetitions', 'Repetitions is a required field.');

        if (!regex.test(e.target.value)) return setError('repetitions', 'Repetitions must be a number.');
    }

    return (
            <TextField
                id="repetitions"
                label="Repetitions"
                variant="outlined"
                required
                error={Boolean(error)}
                helperText={error}
                size='small'
                type='text'
                onChange={validateChange}
                value={repetitions}
                {...rest}
            />
    );
}

export default RepetitionInput;
