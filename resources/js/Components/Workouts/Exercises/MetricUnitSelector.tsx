import { MetricUnit, Option } from "@/types";
import { Autocomplete, TextField } from "@mui/material";

// TODO: Fix typing for setData and setError methods, consider adding style prop?
// TODO: Implement set error and error display on this component
interface MetricUnitSelectorProps {
    metricUnitOptions: Array<Option | []>;
    selectedMetricUnit: MetricUnit | null;
    setData: (arg0: any, arg2: any) => void;
};

const MetricUnitSelector: React.FC<MetricUnitSelectorProps> = ({ metricUnitOptions, selectedMetricUnit, setData }) => {

    const handleChange = (event: any, newValue: Option | null) => {
        setData('selected_exercise_metric_unit', newValue);
    }

    return (

        <Autocomplete
            id='exercise'
            size='small'
            options={metricUnitOptions}
            getOptionLabel={(option) => option?.name || ''}
            value={selectedMetricUnit}
            onChange={handleChange}
            isOptionEqualToValue={(option, value) => option.id === value?.id} // TODO: Understand if this is relevant
            renderInput={ (params) => <TextField required variant="standard" placeholder='EX: LBs' {...params} label="Exercise Metric" /> }
            sx={{ my: 1, minWidth: '150px' }}
        />
    );
}

export default MetricUnitSelector;