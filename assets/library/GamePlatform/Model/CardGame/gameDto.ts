import GameDto from '../Core/gameDto';
import CardGamePlayerDto from './playerDto';
import PlayerPosition from './playerPosition';
import CardGameTeam from './cardGameTeam'

interface CardGameDto extends GameDto {
    players: CardGamePlayerDto[];
    validBids: any;
    
    currentPlayer: PlayerPosition;
    winner: CardGameTeam;
    
    RoundNumber: number;
    FirstToPlayInTheRound: PlayerPosition;
    
    SouthNorthPoints: number;
    EastWestPoints: number;
    
    MyCards: any;
    Bids: any;
    CurrentContract: any;
    
    deck: any;
    pile: any;
    teamsTricks: any;
}

export default CardGameDto;
