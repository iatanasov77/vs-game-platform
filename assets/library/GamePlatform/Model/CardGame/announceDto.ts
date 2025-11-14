import PlayerPosition from './playerPosition';
import AnnounceType from './announceType';
import CardDto from './cardDto';

interface AnnounceDto
{
    Player: PlayerPosition;
    Type: AnnounceType;
    Card: CardDto;
}

export default AnnounceDto;
