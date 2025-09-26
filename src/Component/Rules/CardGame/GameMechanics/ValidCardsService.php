<?php namespace App\Component\Rules\CardGame\GameMechanics;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\BidType;
use App\Component\Type\CardSuit;
use App\Component\Rules\CardGame\Helper;
use App\Component\Manager\AbstractGameManager;

class ValidCardsService
{
    use Helper;
    
    public function GetValidCards( Collection $playerCards, BidType $contract, Collection $currentTrickActions ): Collection
    {
        if ( $currentTrickActions->count() == 0 || $playerCards->count() == 1 ) {
            // The player is first and can play any card or has only 1 card available
            return $playerCards;
        }
        
        $firstCardSuit = $currentTrickActions[0]->Card->Suit;
        
        // Playing AllTrumps
        if ( $contract->HasFlag( BidType::AllTrumps ) ) {
            return $this->GetValidCardsForAllTrumps( $playerCards, $currentTrickActions, $firstCardSuit );
        }
        
        // Playing NoTrumps
        if ( $contract->HasFlag( BidType::NoTrumps ) ) {
            return $this->GetValidCardsForNoTrumps( $playerCards, $firstCardSuit );
        }
        
        // Playing Clubs, Diamonds, Hearts or Spades
        $trumpSuit = $contract->ToCardSuit();
        if ( $firstCardSuit == $trumpSuit ) {
            // Trump card played first
            return $this->GetValidCardsForAllTrumps( $playerCards, $currentTrickActions, $firstCardSuit );
        }
        
        // Playing Clubs, Diamonds, Hearts or Spades and non-trump card played first
        return $this->GetValidCardsForTrumpWhenNonTrumpIsPlayedFirst(
            $playerCards,
            $trumpSuit,
            $currentTrickActions,
            $firstCardSuit
        );
    }
    
    // For all trumps the player should play bigger card from the same suit if available.
    // If bigger card is not available, the player should play any card of the same suit if available.
    private function GetValidCardsForAllTrumps(
        Collection $playerCards,
        Collection $currentTrickActions,
        CardSuit $firstCardSuit
    ): Collection {
    
        if ( $playerCards->HasAnyOfSuit( $firstCardSuit ) ) {
            $biggestCard = $this->BiggestTrumpCard( $currentTrickActions, $firstCardSuit );
            
            $biggerPlayerCards  = $playerCards->filter(
                function( $entry ) use ( $firstCardSuit, $biggestCard ) {
                    return $entry->Suit == $firstCardSuit && $entry->TrumpOrder > $biggestCard->TrumpOrder;
                }
            );
            if ( $biggerPlayerCards->count() ) {
                // Has bigger card(s)
                return $biggerPlayerCards;
            }
            
            // Any other card from the same suit
            return $playerCards->filter(
                function( $entry ) use ( $firstCardSuit ) {
                    return $entry->Suit == $firstCardSuit;
                }
            );
        }
        
        // No card of the same suit available
        return $playerCards;
    }
    
    // For no trumps the player should play card from the same suit if available, else any card is allowed.
    private function GetValidCardsForNoTrumps( Collection $playerCards, CardSuit $firstCardSuit ): Collection
    {
        return $playerCards.HasAnyOfSuit( $firstCardSuit )
                ? new CardCollection(playerCards, x => x.Suit == firstCardSuit)
                : playerCards;
    }
    
    private function GetValidCardsForTrumpWhenNonTrumpIsPlayedFirst(
        Collection $playerCards,
        CardSuit $trumpSuit,
        Collection $currentTrickActions,
        CardSuit $firstCardSuit
    ): Collection {
    
        if ( $playerCards.HasAnyOfSuit( $firstCardSuit ) ) {
            // If the player has the same card suit, he should play a card from the suit
            return $playerCards->filter(
                function( $entry ) use ( $firstCardSuit ) {
                    return $entry->Suit == $firstCardSuit;
                }
            );
        }
        
        if ( ! $playerCards.HasAnyOfSuit( $trumpSuit ) ) {
            // The player doesn't have any trump card or card from the played suit
            return $playerCards;
        }
        
        $currentPlayerTeamIsCurrentTrickWinner = false;
        if ( $currentTrickActions->count() > 1 ) {
            // The teammate played card
            $trumpSuitActions  = $currentTrickActions->filter(
                function( $entry ) use ( $trumpSuit ) {
                    return $entry->Card->Suit == $trumpSuit;
                }
            );
            
            $orderedTrickActions = $this->OrderTrickActionsByCardNoTrumpOrder(
                    $currentTrickActions,
                    AbstractGameManager::COLLECTION_ORDER_DESC
            );
            $biggestCard = $trumpSuitActions->count()
                            ? $this->BiggestTrumpCard( $currentTrickActions, $trumpSuit )
                            : $orderedTrickActions->first()->Card;
            
            if ( $currentTrickActions[$currentTrickActions->count() - 2]->Card == $biggestCard ) {
                // The teammate has the best card in current trick
                $currentPlayerTeamIsCurrentTrickWinner = true;
            }
        }
        
        // The player has trump card
        if ( $currentPlayerTeamIsCurrentTrickWinner ) {
            // The current trick winner is the player or his teammate.
            // The player is not obligatory to play any trump
            return $playerCards;
        }
        
        // The current trick winner is the rivals of the current player
        $trumpSuitActions  = $currentTrickActions->filter(
            function( $entry ) use ( $trumpSuit ) {
                return $entry->Card->Suit == $trumpSuit;
            }
        );
        if ( $trumpSuitActions->count() ) {
            // Someone of the rivals has played trump card and is winning the trick
            $biggestTrumpCard = $this->BiggestTrumpCard( $currentTrickActions, $trumpSuit );
            $biggerPlayerCards  = $playerCards->filter(
                function( $entry ) use ( $trumpSuit, $biggestTrumpCard ) {
                    return $entry->Suit == $trumpSuit && $entry->TrumpOrder > $biggestTrumpCard->TrumpOrder;
                }
            );
            if ( $biggerPlayerCards->count() ) {
                // The player has bigger trump card(s) and should play one of them
                return $biggerPlayerCards;
            }
            
            // The player hasn't any bigger trump card so he can play any card
            return $playerCards;
        }
        
        // No one played trump card, but the player should play one of them
        return $playerCards->filter(
            function( $entry ) use ( $trumpSuit ) {
                return $entry->Suit == $trumpSuit;
            }
        );
    }
    
    private function BiggestTrumpCard( Collection $currentTrickActions, CardSuit $firstCardSuit ): Card
    {
        $bestCard = $currentTrickActions[0]->Card;
        if (
            $currentTrickActions->count() > 1
            && $currentTrickActions[1]->Card->Suit == $firstCardSuit
            && $currentTrickActions[1]->Card->TrumpOrder > $bestCard->TrumpOrder
        ) {
            $bestCard = $currentTrickActions[1]->Card;
        }
        
        if (
            $currentTrickActions->count() > 2
            && $currentTrickActions[2]->Card->Suit == $firstCardSuit
            && $currentTrickActions[2]->Card->TrumpOrder > $bestCard->TrumpOrder
        ) {
            $bestCard = $currentTrickActions[2]->Card;
        }
        
        return $bestCard;
    }
}
