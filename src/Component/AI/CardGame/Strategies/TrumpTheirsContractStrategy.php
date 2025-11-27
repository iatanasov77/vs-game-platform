<?php namespace App\Component\AI\CardGame\Strategies;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use App\Component\Type\PlayerPosition;
use App\Component\Type\BridgeBeloteCardType as CardType;
use App\Component\Type\BidType;
use App\Component\Rules\CardGame\Context\PlayerPlayCardContext;
use App\Component\Rules\CardGame\PlayCardAction;
use App\Component\Rules\CardGame\Card;
use App\Component\Rules\CardGame\BidTypeExtensions;
use App\Component\Rules\CardGame\PlayerPositionExtensions;

class TrumpTheirsContractStrategy implements IPlayStrategy
{
    public function PlayFirst( PlayerPlayCardContext $context, Collection $playedCards ): PlayCardAction
    {
        $suit = BidType::fromBitMaskValue( $context->CurrentContract->Type->get() );
        $trumpSuit = BidTypeExtensions::ToCardSuit( $suit );
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
        
        $availableCardsToPlayIterator = $context->AvailableCardsToPlay->getIterator();
        $availableCardsToPlayIterator->uasort( function ( $a, $b ) {
            return $a->NoTrumpOrder <=> $b->NoTrumpOrder;
        });
        $availableCards = new ArrayCollection( \iterator_to_array( $availableCardsToPlayIterator ) );
        
        return new PlayCardAction( $availableCards->first() ); // .Lowest(x => x.Suit == trumpSuit ? (x.TrumpOrder + 8) : x.NoTrumpOrder)
    }
    
    public function PlaySecond( PlayerPlayCardContext $context, Collection $playedCards ): PlayCardAction
    {
        $availableCardsToPlayIterator = $context->AvailableCardsToPlay->getIterator();
        $availableCardsToPlayIterator->uasort( function ( $a, $b ) {
            return $a->NoTrumpOrder <=> $b->NoTrumpOrder;
        });
        $availableCards = new ArrayCollection( \iterator_to_array( $availableCardsToPlayIterator ) );
        
        return new PlayCardAction( $availableCards->first() ); // .Lowest(x => x.Suit == trumpSuit ? (x.TrumpOrder + 8) : x.NoTrumpOrder)
    }
    
    public function PlayThird( PlayerPlayCardContext $context, Collection $playedCards, PlayerPosition $trickWinner ): PlayCardAction
    {
        $availableCardsToPlayIterator = $context->AvailableCardsToPlay->getIterator();
        $availableCardsToPlayIterator->uasort( function ( $a, $b ) {
            return $a->NoTrumpOrder <=> $b->NoTrumpOrder;
        });
        $availableCards = new ArrayCollection( \iterator_to_array( $availableCardsToPlayIterator ) );
        
        return new PlayCardAction( $availableCards->first() ); // .Lowest(x => x.Suit == trumpSuit ? (x.TrumpOrder + 8) : x.NoTrumpOrder)
    }
    
    public function PlayFourth( PlayerPlayCardContext $context, Collection $playedCards, PlayerPosition $trickWinner ): PlayCardAction
    {
        $suit = BidType::fromBitMaskValue( $context->CurrentContract->Type->get() );
        $trumpSuit = BidTypeExtensions::ToCardSuit( $suit );
        $cardsToPlay = $context->AvailableCardsToPlay->filter(
            function( $entry ) use ( $trumpSuit ) {
                return $entry->Suit != $trumpSuit && $entry->Type != CardType::Ace;
            }
        );
        if ( PlayerPositionExtensions::IsInSameTeamWith( $trickWinner, $context->MyPosition ) && $cardsToPlay->count() ) {
            $availableCardsToPlayIterator = $cardsToPlay->getIterator();
            $availableCardsToPlayIterator->uasort( function ( $a, $b ) {
                return $a->NoTrumpOrder <=> $b->NoTrumpOrder;
            });
            $availableCards = new ArrayCollection( \iterator_to_array( $availableCardsToPlayIterator ) );
            
            return new PlayCardAction( $availableCards->first() ); // .Lowest(x => x.Suit == trumpSuit ? (x.TrumpOrder + 8) : x.NoTrumpOrder)
        }
        
        $availableCardsToPlayIterator = $context->AvailableCardsToPlay->getIterator();
        $availableCardsToPlayIterator->uasort( function ( $a, $b ) {
            return $a->NoTrumpOrder <=> $b->NoTrumpOrder;
        });
        $availableCards = new ArrayCollection( \iterator_to_array( $availableCardsToPlayIterator ) );
        
        return new PlayCardAction( $availableCards->first() ); // .Lowest(x => x.Suit == trumpSuit ? (x.TrumpOrder + 8) : x.NoTrumpOrder)
    }
}
