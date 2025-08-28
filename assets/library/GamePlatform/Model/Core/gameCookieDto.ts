import PlayerColor from './playerColor';
import PlayerPosition from '../CardGame/playerPosition';

interface GameCookieDto
{
    id: string;
    game: string;
    
    color?: PlayerColor;
    position?: PlayerPosition;
    
    roomSelected: boolean;
}

export default GameCookieDto;
