import GameDto from '../Core/gameDto';
import CardGamePlayerDto from './playerDto';
import CardDto from './cardDto';
import PlayerPosition from './playerPosition';
import CardGameTeam from './cardGameTeam'

interface CardGameDto extends GameDto {
    players: CardGamePlayerDto[];
    validBids: any;
    validCards: any;
    contract: any;
    
    currentPlayer: PlayerPosition;
    winner: CardGameTeam;
    
    RoundNumber: number;
    FirstToPlayInTheRound: PlayerPosition;
    
    SouthNorthPoints: number;
    EastWestPoints: number;
    
    MyCards: any;
    Bids: any;
    
    deck: CardDto[];
    pile: any;
    teamsTricks: any;
}

export default CardGameDto;
