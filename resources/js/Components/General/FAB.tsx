import { Fab, Icon } from '@mui/material';

interface FABProps {
    handleClick: () => void;
    iconName?: string;
    style?: React.CSSProperties;
    children?: React.ReactNode;
    rest?: any
};

const defaultStyles: React.CSSProperties = {
    position: 'absolute',
    right: 32,
    bottom: 32,
};

const FAB: React.FC<FABProps> = ({handleClick, style, iconName='add', ...rest}) => {

    const combinedStyles = {
        ...defaultStyles,
        ...style
    };

    return (
        <Fab color="primary" style={combinedStyles} aria-label="add" onClick={handleClick} {...rest}>
            <Icon>
                {iconName}
            </Icon>
        </Fab>
    );
}

export default FAB;