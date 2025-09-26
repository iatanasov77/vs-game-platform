import PlayerDto from '../Core/playerDto';
import PlayerPosition from './playerPosition';

interface CardGamePlayerDto extends PlayerDto {
    playerPosition: PlayerPosition;
}

export default CardGamePlayerDto;
