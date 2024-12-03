import {
    TextField
} from '@mui/material';
import React from 'react';

interface RepitionInputProps {
    repitions: number | null;
    setData: (arg0: any, arg2: any) => void;
    setError: (arg0: any, arg2: any) => void;
    error: string | undefined;
};

const RepitionInput: React.FC<RepitionInputProps> = ({ repitions, setData, setError, error }) => {

    const validateChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const regex = /^\d+$/;

        setError('repitions', '');
        setData('repitions', e.target.value);

        if (e.target.value.length === 0) return setError('repitions', 'Repitions is a required field.');

        if (!regex.test(e.target.value)) return setError('repitions', 'Repitions must be a number.');
    }

    return (
            <TextField
                id="repitions"
                label="Repitions"
                variant="outlined"
                required
                error={Boolean(error)}
                helperText={error}
                size='small'
                type='text'
                onChange={validateChange}
                value={repitions}
            />
    );
}

export default RepitionInput;
