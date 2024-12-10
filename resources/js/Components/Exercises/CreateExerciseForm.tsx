import { Option } from '@/types';
import {
    Autocomplete,
    Stack,
    TextField
} from '@mui/material';

interface CreateExerciseFormProps {
    exerciseOptions: Array<Option | []>;
    data: Option | null;
    setData: (arg1: Option | null) => void;
};

const CreateExerciseForm: React.FC<CreateExerciseFormProps> = ({ exerciseOptions, data, setData }) => {

    const handleChange = (event: any, newValue: Option | null) => {
        setData(newValue);
    }

    return (
        <Stack sx={{ p: 4 }}>
            <Autocomplete
                id='exercise'
                size='small'
                options={exerciseOptions}
                getOptionLabel={(option) => option?.name || ''}
                value={data}
                onChange={handleChange}
                isOptionEqualToValue={(option, value) => option.id === value?.id} // TODO: Understand if this is relevant
                renderInput={ (params) => <TextField required variant="outlined" placeholder='EX: Bench Press' {...params} label="Exercise" /> }
                sx={{ my: 1 }}
            />
        </Stack>
    );
}

export default CreateExerciseForm;