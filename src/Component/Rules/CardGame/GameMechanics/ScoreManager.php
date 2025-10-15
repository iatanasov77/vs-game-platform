<?php namespace App\Component\Rules\CardGame\GameMechanics;

use BitMask\EnumBitMask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\GameLogger;
use App\Component\Type\GameState;
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;

use App\Component\Rules\CardGame\Game;
use App\Component\Rules\CardGame\Card;
use App\Component\Rules\CardGame\Deck;
use App\Component\Rules\CardGame\Bid;
use App\Component\Rules\CardGame\PlayerPositionExtensions;
use App\Component\Rules\CardGame\CardExtensions;

class ScoreManager
{
    /** @var Game */
    private Game $game;
    
    /** @var GameLogger */
    private  $logger;
    
    public function __construct( Game $game, GameLogger $logger )
    {
        $this->game = $game;
        $this->logger = $logger;
    }
    
    public function GetScore(
        Bid $contract,
        Collection $southNorthTricks,
        Collection $eastWestTricks,
        Collection $announces,
        int $hangingPoints,
        ?PlayerPosition $lastTrickWinner
    ): RoundResult {
        $result = new RoundResult( $contract );
        
        // Sum all south-north points
        $activeSouthNorthAnnounces = $announces->filter(
            function( $entry ) {
                return $entry->IsActive == true &&
                    ( $entry->Player == PlayerPosition::South || $entry->Player == PlayerPosition::North );
            }
        );
        
        foreach( $activeSouthNorthAnnounces as $ann ) {
            $result->SouthNorthTotalInRoundPoints += $ann->Value;
        }
        
        foreach( $southNorthTricks as $card ) {
            $result->SouthNorthTotalInRoundPoints += CardExtensions::GetValue( $card, $contract->Type );
        }
            
            
        if ( $lastTrickWinner == PlayerPosition::South || $lastTrickWinner == PlayerPosition::North ) {
            // Last 10
            $result->SouthNorthTotalInRoundPoints += 10;
        }
            
        // Sum all east-west points
        $activeEastWestAnnounces = $announces->filter(
            function( $entry ) {
                return $entry->IsActive == true &&
                ( $entry->Player == PlayerPosition::East || $entry->Player == PlayerPosition::West );
            }
        );
        
        foreach( $activeEastWestAnnounces as $ann ) {
            $result->EastWestTotalInRoundPoints += $ann->Value;
        }
        
        foreach( $eastWestTricks as $card ) {
            $result->EastWestTotalInRoundPoints += CardExtensions::GetValue( $card, $contract->Type );
        }
        
        
        if ( $lastTrickWinner == PlayerPosition::East || $lastTrickWinner == PlayerPosition::West ) {
            // Last 10
            $result->EastWestTotalInRoundPoints += 10;
        }
        
        // Double no trump points
        if ( $contract->Type->has( BidType::NoTrumps ) ) {
            $result->SouthNorthTotalInRoundPoints *= 2;
            $result->EastWestTotalInRoundPoints *= 2;
        }
        
        // 9 points for no tricks
        if ( $southNorthTricks->count() == 0 ) {
            $result->EastWestTotalInRoundPoints += 90;
            $result->NoTricksForOneOfTheTeams = true;
        }
        
        if ( $eastWestTricks->count() == 0 ) {
            $result->SouthNorthTotalInRoundPoints += 90;
            $result->NoTricksForOneOfTheTeams = true;
        }
        
        // Check if game is inside or hanging
        if ( $contract->Type->has( BidType::Double ) || $contract->Type->has( BidType::ReDouble ) ) {
            $coefficient = $contract->Type->has( BidType::ReDouble ) ? 4 : 2;
            if ( $result->NoTricksForOneOfTheTeams ) {
                // When no tricks - double and re-double doesn't take place
                $coefficient = 1;
            }
            
            $allPoints = $result->SouthNorthTotalInRoundPoints + $result->EastWestTotalInRoundPoints;
            if ( $result->SouthNorthTotalInRoundPoints > $result->EastWestTotalInRoundPoints ) {
                $result->SouthNorthPoints += ( self::RoundPoints( $allPoints ) * $coefficient) + hangingPoints;
            } else if ( $result->EastWestTotalInRoundPoints > $result->SouthNorthTotalInRoundPoints ) {
                $result->EastWestPoints += ( self::RoundPoints( $allPoints ) * $coefficient ) + hangingPoints;
            } else if ( $result->SouthNorthTotalInRoundPoints == $result->EastWestTotalInRoundPoints ) {
                $result->HangingPoints = ( self::RoundPoints( $allPoints ) * $coefficient ) + hangingPoints;
            }
        } else if (
            ( $contract->Player == PlayerPosition::South || $contract->Player == PlayerPosition::North ) &&
            $result->SouthNorthTotalInRoundPoints < $result->EastWestTotalInRoundPoints
        ) {
            // Inside -> all points goes to the other team
            $result->EastWestPoints += self::RoundPoints( $result->SouthNorthTotalInRoundPoints + $result->EastWestTotalInRoundPoints ) + $hangingPoints;
        } else if (
            ( $contract->Player == PlayerPosition::South || $contract->Player == PlayerPosition::North )
            && $result->SouthNorthTotalInRoundPoints == $result->EastWestTotalInRoundPoints
        ) {
            // The other team gets its half of the points
            $result->EastWestPoints += self::RoundPointsByBidType( $contract->Type, $result->EastWestTotalInRoundPoints, true );
            
            // "Hanging" points are added to current hanging points
            $result->HangingPoints = $hangingPoints + self::RoundPointsByBidType(
                $contract->Type,
                $result->SouthNorthTotalInRoundPoints,
                false
            );
        } else if (
            ( $contract->Player == PlayerPosition::East || $contract->Player == PlayerPosition::West )
            && $result->EastWestTotalInRoundPoints < $result->SouthNorthTotalInRoundPoints
        ) {
            // Inside -> all points goes to the other team
            $result->SouthNorthPoints += self::RoundPoints( $result->SouthNorthTotalInRoundPoints + $result->EastWestTotalInRoundPoints ) + $hangingPoints;
        } else if (
            ( $contract->Player == PlayerPosition::East || $contract->Player == PlayerPosition::West )
            && $result->SouthNorthTotalInRoundPoints == $result->EastWestTotalInRoundPoints
        ) {
            // The other team gets its half of the points
            $result->SouthNorthPoints += self::RoundPointsByBidType( $contract->Type, $result->SouthNorthTotalInRoundPoints, true );
            
            // "Hanging" points are added to current hanging points
            $result->HangingPoints = hangingPoints + self::RoundPointsByBidType(
                $contract->Type,
                $result->EastWestTotalInRoundPoints,
                false
            );
        } else {
            // Normal game
            $result->SouthNorthPoints = self::RoundPointsByBidType(
                $contract->Type,
                $result->SouthNorthTotalInRoundPoints,
                $result->SouthNorthTotalInRoundPoints > $result->EastWestTotalInRoundPoints
            );
            
            $result->EastWestPoints = self::RoundPointsByBidType(
                $contract->Type,
                $result->EastWestTotalInRoundPoints,
                $result->EastWestTotalInRoundPoints > $result->SouthNorthTotalInRoundPoints
            );
            
            if ( $result->SouthNorthTotalInRoundPoints > $result->EastWestTotalInRoundPoints ) {
                $result->SouthNorthPoints += $hangingPoints;
            } else if ( $result->EastWestTotalInRoundPoints > $result->SouthNorthTotalInRoundPoints ) {
                $result->EastWestPoints += $hangingPoints;
            }
        }
                
        return $result;
    }
    
    private static function RoundPointsByBidType( EnumBitMask $bidType, int $points, bool $winner ): int
    {
        // All trumps
        if ( $bidType->has( BidType::AllTrumps ) ) {
            if ( $points % 10 > 4 ) {
                return ( $points / 10 ) + 1;
            }
            
            if ( $points % 10 == 4 ) {
                if ( $winner ) {
                    return $points / 10;
                }
                
                return ( $points / 10 ) + 1;
            }
            
            return $points / 10;
        }
        
        // No trumps
        if ( $bidType->has( BidType::NoTrumps ) ) {
            return self::RoundPoints( $points );
        }
        
        // Trump
        if ( $points % 10 > 6 ) {
            return ( $points / 10 ) + 1;
        }
        
        if ( $points % 10 == 6 ) {
            if ( $winner ) {
                return $points / 10;
            }
            
            return ( $points / 10 ) + 1;
        }
        
        return $points / 10;
    }
    
    private static function RoundPoints( int $points ): int
    {
        return ( int ) \round( $points / 10 );
    }
}
