import GameDto from '../Core/gameDto';
import CardGamePlayerDto from './playerDto';
import PlayerPosition from './playerPosition';
import CardGameTeam from './cardGameTeam'

interface CardGameDto extends GameDto {
    players: CardGamePlayerDto[];
    validBids: any;
    validCards: any;
    contract: any;
    
    currentPlayer: PlayerPosition;
    winner: CardGameTeam;
    thinkTime: number;
    
    FirstToPlayInTheRound: PlayerPosition;
    RoundNumber: number;
    TrickNumber: number;
    
    SouthNorthPoints: number;
    EastWestPoints: number;
    
    MyCards: any;
    Bids: any;
}

export default CardGameDto;
