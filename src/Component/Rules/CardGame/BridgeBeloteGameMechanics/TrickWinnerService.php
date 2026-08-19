<?php namespace App\Component\Rules\CardGame\BridgeBeloteGameMechanics;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidTrump;
use App\Component\Rules\CardGame\Bid;
use App\Component\Rules\CardGame\BidTrumpExtensions;

class TrickWinnerService
{
    public function GetWinner( Bid $contract, Collection $trickActions ): PlayerPosition
    {
        $firstCard = $trickActions[0]->Card;
        $bestAction = $trickActions[0];
        if ( $contract->Trump->has( BidTrump::AllTrumps ) ) {
            for ( $i = 1; $i < $trickActions->count(); $i++ ) {
                if (
                    $trickActions[$i]->Card->Suit == $firstCard->Suit
                    && $trickActions[$i]->Card->TrumpOrder > $bestAction->Card->TrumpOrder
                ) {
                    $bestAction = $trickActions[$i];
                }
            }
        } else if ( $contract->Trump->has( BidTrump::NoTrumps ) ) {
            for ( $i = 1; $i < $trickActions->count(); $i++ ) {
                if (
                    $trickActions[$i]->Card->Suit == $firstCard->Suit
                    && $trickActions[$i]->Card->NoTrumpOrder > $bestAction->Card->NoTrumpOrder
                ) {
                    $bestAction = $trickActions[$i];
                }
            }
        } else {
            $suit = BidTrump::fromBitMaskValue( $contract->Trump->get() );
            $trumpSuit = BidTrumpExtensions::ToCardSuit( $suit );
            //// TODO: Remove this check and merge conditions
            
            $trumpSuitActions  = $trickActions->filter(
                function( $entry ) use ( $trumpSuit ) {
                    return $entry->Card->Suit == $trumpSuit;
                }
            );
            if ( $trumpSuitActions->count() ) {
                // Trump in the trick cards
                for ( $i = 1; $i < $trickActions->count(); $i++) {
                    if ( $trickActions[$i]->Card->Suit == $trumpSuit ) {
                        if ( $bestAction->Card->Suit != $trumpSuit ) {
                            $bestAction = $trickActions[$i];
                        } else if ( $trickActions[$i]->Card->TrumpOrder > $bestAction->Card->TrumpOrder) {
                            $bestAction = $trickActions[$i];
                        }
                    }
                }
            } else {
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
