<?php namespace App\Component\Manager\GameMechanics;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;
use App\Component\Rules\CardGame\Bid;

class TrickWinnerService
{
    public function GetWinner( Bid $contract, Collection $trickActions ): PlayerPosition
    {
        $firstCard = $trickActions[0]->Card;
        $bestAction = $trickActions[0];
        if ( $contract->Type->HasFlag( BidType::AllTrumps ) ) {
            for ( $i = 1; $i < $trickActions->count(); $i++ ) {
                if (
                    $trickActions[$i]->Card->Suit == $firstCard->Suit
                    && $trickActions[$i]->Card->TrumpOrder > $bestAction->Card->TrumpOrder
                ) {
                    $bestAction = $trickActions[$i];
                }
            }
        } else if ( $contract->Type->HasFlag( BidType::NoTrumps ) ) {
            for ( $i = 1; $i < $trickActions->count(); $i++ ) {
                if (
                    $trickActions[$i]->Card->Suit == $firstCard->Suit
                    && $trickActions[$i]->Card->NoTrumpOrder > $bestAction->Card->NoTrumpOrder
                ) {
                    $bestAction = $trickActions[$i];
                }
            }
        } else {
            $trumpSuit = $contract->Type->ToCardSuit();
            //// TODO: Remove this check and merge conditions
            
            $trumpSuitActions  = $trickActions->filter(
                function( $entry ) use ( $trumpSuit ) {
                    return $entry->Card->Suit == $trumpSuit;
                }
            );
            if ( $trumpSuitActions->count() ) {
                // Trump in the trick cards
                for ( $i = 1; $i < $trickActions->count(); $i++)
                {
                    if ( $trickActions[$i]->Card->Suit == $trumpSuit ) {
                        if ( $bestAction->Card->Suit != $trumpSuit ) {
                            $bestAction = $trickActions[$i];
                        } else if ( $trickActions[$i]->Card->TrumpOrder > $bestAction->Card->TrumpOrder) {
                            $bestAction = $trickActions[$i];
                        }
                    }
                }
            }
            else
            {
                // No trick in the cards
                for ( $i = 1; $i < $trickActions->count(); $i++ ) {
                    if (
                        $trickActions[$i]->Card->Suit == $firstCard->Suit
                        && $trickActions[$i]->Card->NoTrumpOrder > $bestAction->Card->NoTrumpOrder
                    ) {
                        $bestAction = $trickActions[$i];
                    }
                }
            }
        }
        
        return $bestAction->Player;
    }
}
