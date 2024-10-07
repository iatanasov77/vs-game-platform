import ActionDto from './actionDto';
import ConnectionDto from '_@/GamePlatform/Model/BoardGame/connectionDto';

interface ConnectionInfoActionDto extends ActionDto {
    connection: ConnectionDto;
}

export default ConnectionInfoActionDto;
