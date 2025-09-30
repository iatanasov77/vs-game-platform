<?php namespace App\Component\AI\CardGame\Strategies;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerPosition;
use App\Component\Type\CardType;
use App\Component\Rules\CardGame\Context\PlayerPlayCardContext;
use App\Component\Rules\CardGame\PlayCardAction;
use App\Component\Rules\CardGame\Card;
use App\Component\Rules\CardGame\BidTypeExtensions;

class TrumpOursContractStrategy implements IPlayStrategy
{
    public function PlayFirst( PlayerPlayCardContext $context, Collection $playedCards ): PlayCardAction
    {
        $trumpSuit = BidTypeExtensions::ToCardSuit( $context->CurrentContract->Type );
        $playedCardsFromTrump = $playedCards->filter(
            function( $entry ) use ( $trumpSuit ) {
                return $entry->Suit == $trumpSuit;
            }
        )->count();
        $myCardsFromTrump = $context->MyCards->filter(
            function( $entry ) use ( $trumpSuit ) {
                return $entry->Suit == $trumpSuit;
            }
        )->count();
        
        if ( ( $playedCardsFromTrump + $myCardsFromTrump ) == 8 ) {
            // No trump cards in other players
            foreach ( $context->AvailableCardsToPlay as $card ) {
                if ( $card->Suit != $trumpSuit && $card->Type == CardType::Ace )
                {
                    return new PlayCardAction( $card );
                }
                
                if ( $card->Suit != $trumpSuit && $card->Type == CardType::Ten
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
                ) {
                    return new PlayCardAction( $card );
                }
                
                if ( $card->Suit != $trumpSuit && $card->Type == CardType::King
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ten ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
                ) {
                    return new PlayCardAction( $card );
                }
                
                if ( $card->Suit != $trumpSuit && $card->Type == CardType::Queen
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::King ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ten ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
                ) {
                    return new PlayCardAction( $card );
                }
                
                if ( $card->Suit != $trumpSuit && $card->Type == CardType::Jack
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Queen ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::King ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ten ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
                ) {
                    return new PlayCardAction( $card );
                }
                
                if ( $card->Suit != $trumpSuit && $card->Type == CardType::Nine
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Jack ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Queen ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::King ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ten ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
                ) {
                    return new PlayCardAction( $card );
                }
                
                if ( $card->Suit != $trumpSuit && $card->Type == CardType::Eight
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Nine ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Jack ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Queen ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::King ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ten ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
                ) {
                    return new PlayCardAction( $card );
                }
                
                if ( $card->Suit != $trumpSuit && $card->Type == CardType::Seven
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Eight ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Nine ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Jack ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Queen ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::King ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ten ) )
                    && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
                ) {
                    return new PlayCardAction( $card );
                }
            }
        }
        
        //// if (context.AvailableCardsToPlay.HasAnyOfSuit(context.CurrentContract.Type.ToCardSuit()))
        //// {
        ////     Interlocked.Increment(ref GlobalCounters.Counters[1]);
        ////     return new PlayCardAction(
        ////         context.AvailableCardsToPlay.Where(x => x.Suit == context.CurrentContract.Type.ToCardSuit())
        ////             .Highest(x => x.TrumpOrder));
        //// }
        
        $cardsToPlayIterator = $context->AvailableCardsToPlay->getIterator();
        $cardToPlay = $cardsToPlayIterator->uasort( function ( $a, $b ) use ( $trumpSuit ) {
            return $b->NoTrumpOrder <=> $a->NoTrumpOrder;
        })->first();
        
        return new PlayCardAction( $cardToPlay ); // .Lowest(x => x.Suit == trumpSuit ? (x.TrumpOrder + 8) : x.NoTrumpOrder)
    }
    
    public function PlaySecond( PlayerPlayCardContext $context, Collection $playedCards ): PlayCardAction
    {
        $trumpSuit = BidTypeExtensions::ToCardSuit( $context->CurrentContract->Type );
        $cardsToPlayIterator = $context->AvailableCardsToPlay->getIterator();
        $cardToPlay = $cardsToPlayIterator->uasort( function ( $a, $b ) use ( $trumpSuit ) {
            return $b->NoTrumpOrder <=> $a->NoTrumpOrder;
        })->first();
        
        return new PlayCardAction( $cardToPlay ); // .Lowest(x => x.Suit == trumpSuit ? (x.TrumpOrder + 8) : x.NoTrumpOrder)
    }
    
    public function PlayThird( PlayerPlayCardContext $context, Collection $playedCards, PlayerPosition $trickWinner ): PlayCardAction
    {
        $trumpSuit = BidTypeExtensions::ToCardSuit( $context->CurrentContract->Type );
        $cardsToPlayIterator = $context->AvailableCardsToPlay->getIterator();
        $cardToPlay = $cardsToPlayIterator->uasort( function ( $a, $b ) use ( $trumpSuit ) {
            return $b->NoTrumpOrder <=> $a->NoTrumpOrder;
        })->first();
        
        return new PlayCardAction( $cardToPlay ); // .Lowest(x => x.Suit == trumpSuit ? (x.TrumpOrder + 8) : x.NoTrumpOrder)
    }
    
    public function PlayFourth( PlayerPlayCardContext $context, Collection $playedCards, PlayerPosition $trickWinner ): PlayCardAction
    {
        $trumpSuit = BidTypeExtensions::ToCardSuit( $context->CurrentContract->Type );
        $cardsToPlay = $context->AvailableCardsToPlay->filter(
            function( $entry ) use ( $trumpSuit ) {
                return $entry->Suit != $trumpSuit && $entry->Type != CardType::Ace;
            }
        );
        if ( $trickWinner->IsInSameTeamWith( $context->MyPosition ) && $cardsToPlay->count() ) {
            $cardsToPlayIterator = $cardsToPlay->getIterator();
            $cardToPlay = $cardsToPlayIterator->uasort( function ( $a, $b ) use ( $trumpSuit ) {
                return $b->NoTrumpOrder <=> $a->NoTrumpOrder;
            })->last();
            
            return new PlayCardAction( $cardToPlay ); // .Highest(x => x.Suit == trumpSuit ? (x.TrumpOrder + 8) : x.NoTrumpOrder)
        }
        
        $cardsToPlayIterator = $context->AvailableCardsToPlay->getIterator();
        $cardToPlay = $cardsToPlayIterator->uasort( function ( $a, $b ) use ( $trumpSuit ) {
            return $b->NoTrumpOrder <=> $a->NoTrumpOrder;
        })->first();
        
        return new PlayCardAction( $cardToPlay ); // .Lowest(x => x.Suit == trumpSuit ? (x.TrumpOrder + 8) : x.NoTrumpOrder)
    }
}
