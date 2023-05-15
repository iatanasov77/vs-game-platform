import GamePlayersIterator from "./GamePlayersIterator";

export interface ICardGame
{
    deck: any;
    players: GamePlayersIterator;
}