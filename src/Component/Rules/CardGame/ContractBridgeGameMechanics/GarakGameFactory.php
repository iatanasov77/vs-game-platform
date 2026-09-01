<?php namespace App\Component\Rules\CardGame\ContractBridgeGameMechanics;

use Doctrine\Common\Collections\Collection;
use Garak\Bridge\Hand;
use Garak\Bridge\Side;
use Garak\Bridge\Table;

use App\Component\GameLogger;
use App\Component\Type\PlayerPosition;
use App\Component\Rules\CardGame\Game;
use App\Component\Type\ContractBridgeCardType;
use App\Component\Type\CardSuit;

final class GarakGameFactory
{
    public static function CreateGame( Game $vsGame, GameLogger $logger ): GarakGame
    {
        /*  
        $debugCards = self::PlayerCardsString( $vsGame->playerCards[PlayerPosition::South->value] );
        $logger->log( "Garak Cards String: {$debugCards}", 'GameManager' );
        */
        
        $north = Hand::createFromString( self::PlayerCardsString( $vsGame->playerCards[PlayerPosition::North->value] ) );
        $east  = Hand::createFromString( self::PlayerCardsString( $vsGame->playerCards[PlayerPosition::East->value] ) );
        $south = Hand::createFromString( self::PlayerCardsString( $vsGame->playerCards[PlayerPosition::South->value] ) );
        $west  = Hand::createFromString( self::PlayerCardsString( $vsGame->playerCards[PlayerPosition::West->value] ) );
        
        $table = new Table( $north, $east, $south, $west, false );
        $game  = new GarakGame( $table, self::GarakSide( $vsGame->firstInRound ) );
        
        $game->join( new GarakPlayer( $vsGame->Players[PlayerPosition::North->value]->Name, $vsGame->Players[PlayerPosition::North->value]->Id ), Side::North );
        $game->join( new GarakPlayer( $vsGame->Players[PlayerPosition::East->value]->Name, $vsGame->Players[PlayerPosition::North->value]->Id ), Side::East );
        $game->join( new GarakPlayer( $vsGame->Players[PlayerPosition::South->value]->Name, $vsGame->Players[PlayerPosition::North->value]->Id ), Side::South );
        $game->join( new GarakPlayer( $vsGame->Players[PlayerPosition::West->value]->Name, $vsGame->Players[PlayerPosition::North->value]->Id ), Side::West );
        
        return $game;
    }
    
    private static function PlayerCardsString( Collection $playerCards ): string
    {
        $cards = [];
        foreach ( $playerCards as $card ) {
            switch( $card->Type ) {
                case ContractBridgeCardType::Ten:
                    $cardType = 'T';
                    break;
                default:
                    $cardType = $card->Type->toString();
            }
            
            switch( $card->Suit ) {
                case CardSuit::Club:
                    $cardSuit = 'c';
                    break;
                case CardSuit::Diamond:
                    $cardSuit = 'd';
                    break;
                case CardSuit::Heart:
                    $cardSuit = 'h';
                    break;
                case CardSuit::Spade:
                    $cardSuit = 's';
                    break;
            }
            
            $cards[] = \sprintf( '%s%s', $cardType, $cardSuit );
        }
        
        return \implode( ',', $cards );
    }
    
    private static function GarakSide( PlayerPosition $position ): Side
    {
        switch ( $position ) {
            case PlayerPosition::North:
                return Side::North;
                break;
            case PlayerPosition::East:
                return Side::East;
                break;
            case PlayerPosition::South:
                return Side::South;
                break;
            case PlayerPosition::West:
                return Side::West;
                break;
            default:
                throw \Exception( 'Invalid Player Position !!!' );
        }
    }
}
