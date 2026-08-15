import ActionDto from './actionDto';
import { ConnectionDto } from '@vankosoft/game-platform';

interface ConnectionInfoActionDto extends ActionDto {
    connection: ConnectionDto;
}

export default ConnectionInfoActionDto;
