import PlayerDto from '../Core/playerDto';
import PlayerPosition from './playerPosition';
import CardDto from './cardDto';

interface CardGamePlayerDto extends PlayerDto {
    playerPosition: PlayerPosition;
    cards: CardDto[];
}

export default CardGamePlayerDto;
