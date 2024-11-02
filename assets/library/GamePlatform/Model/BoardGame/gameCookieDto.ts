import PlayerColor from './playerColor';

interface GameCookieDto
{
    id: string;
    color: PlayerColor;
    game: string;
    roomSelected: boolean;
}

export default GameCookieDto;
