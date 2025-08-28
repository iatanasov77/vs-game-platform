import GameDto from '../Core/gameDto';
import CardGamePlayerDto from './playerDto';
import PlayerPosition from './playerPosition';

interface CardGameDto extends GameDto {
    northPlayer: CardGamePlayerDto;
    southPlayer: CardGamePlayerDto;
    eastPlayer: CardGamePlayerDto;
    westPlayer: CardGamePlayerDto;
    currentPlayer: PlayerPosition;
}

export default CardGameDto;
