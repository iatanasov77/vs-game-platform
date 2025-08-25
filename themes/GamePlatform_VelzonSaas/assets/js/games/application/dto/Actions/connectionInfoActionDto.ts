import ActionDto from './actionDto';
import ConnectionDto from '_@/GamePlatform/Model/Core/connectionDto';

interface ConnectionInfoActionDto extends ActionDto {
    connection: ConnectionDto;
}

export default ConnectionInfoActionDto;
