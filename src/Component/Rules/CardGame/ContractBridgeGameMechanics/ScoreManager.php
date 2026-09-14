<?php namespace App\Component\Rules\CardGame\ContractBridgeGameMechanics;

use BitMask\EnumBitMask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\GameLogger;
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidTrump;

use App\Component\Rules\CardGame\Game;
use App\Component\Rules\CardGame\Bid;
use App\Component\Rules\CardGame\CardExtensions;

/**
 * Manual: https://en.wikipedia.org/wiki/Bridge_scoring
 */
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
        foreach( $southNorthTricks as $card ) {
            // $result->SouthNorthTotalInRoundPoints += CardExtensions::GetValue( $card, $contract->Trump );
        }
            
        // Sum all east-west points
        foreach( $eastWestTricks as $card ) {
            // $result->EastWestTotalInRoundPoints += CardExtensions::GetValue( $card, $contract->Trump );
        }
        
        
        // Double no trump points
        if ( $contract->Trump->has( BidTrump::NoTrumps ) ) {
            // $result->SouthNorthTotalInRoundPoints *= 2;
            // $result->EastWestTotalInRoundPoints *= 2;
        }
        
        // 9 points for no tricks
        if ( $southNorthTricks->count() == 0 && ! $contract->Trump->has( BidTrump::Pass ) ) {
            // $result->EastWestTotalInRoundPoints += 90;
            // $result->NoTricksForOneOfTheTeams = true;
        }
        
        if ( $eastWestTricks->count() == 0 && ! $contract->Trump->has( BidTrump::Pass ) ) {
            // $result->SouthNorthTotalInRoundPoints += 90;
            // $result->NoTricksForOneOfTheTeams = true;
        }
        
        // Check if game is inside or hanging
        if ( $contract->Trump->has( BidTrump::Double ) || $contract->Trump->has( BidTrump::ReDouble ) ) {
            $coefficient = $contract->Trump->has( BidTrump::ReDouble ) ? 4 : 2;
            
        } else if (
            ( $contract->Player == PlayerPosition::South || $contract->Player == PlayerPosition::North )
        ) {
            // Inside -> all points goes to the other team
            
        } else if (
            ( $contract->Player == PlayerPosition::South || $contract->Player == PlayerPosition::North )
        ) {
            // The other team gets its half of the points
            
        } else if (
            ( $contract->Player == PlayerPosition::East || $contract->Player == PlayerPosition::West )
        ) {
            // Inside -> all points goes to the other team
            
        } else if (
            ( $contract->Player == PlayerPosition::East || $contract->Player == PlayerPosition::West )
        ) {
            // The other team gets its half of the points
            
        } else {
            // Normal game
            
        }
        
        return $result;
    }
}
