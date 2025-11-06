<?php namespace App\Component\AI\CardGame\Strategies;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use App\Component\Type\PlayerPosition;
use App\Component\Type\CardType;
use App\Component\Rules\CardGame\Context\PlayerPlayCardContext;
use App\Component\Rules\CardGame\PlayCardAction;
use App\Component\Rules\CardGame\PlayerPositionExtensions;

class NoTrumpsTheirsContractStrategy implements IPlayStrategy
{
    public function PlayFirst( PlayerPlayCardContext $context, Collection $playedCards ): PlayCardAction
    {
        $card = CardHelpers::GetCardThatSurelyWinsATrickInNoTrumps(
            $context->AvailableCardsToPlay,
            $context->MyCards,
            $playedCards
        );
        if ( $card != null ) {
            return new PlayCardAction( $card );
        }
        
        $availableCardsToPlayIterator = $context->AvailableCardsToPlay->getIterator();
        $availableCardsToPlayIterator->uasort( function ( $a, $b ) {
            return $a->NoTrumpOrder <=> $b->NoTrumpOrder;
        });
        $availableCards = new ArrayCollection( \iterator_to_array( $availableCardsToPlayIterator ) );
        
        return new PlayCardAction( $availableCards->first() ); // .Lowest(x => x.NoTrumpOrder)
    }
    
    public function PlaySecond( PlayerPlayCardContext $context, Collection $playedCards ): PlayCardAction
    {
        $availableCardsToPlayIterator = $context->AvailableCardsToPlay->getIterator();
        $availableCardsToPlayIterator->uasort( function ( $a, $b ) {
            return $a->NoTrumpOrder <=> $b->NoTrumpOrder;
        });
        $availableCards = new ArrayCollection( \iterator_to_array( $availableCardsToPlayIterator ) );
        
        return new PlayCardAction( $availableCards->first() ); // .Lowest(x => x.NoTrumpOrder)
    }
    
    public function PlayThird( PlayerPlayCardContext $context, Collection $playedCards, PlayerPosition $trickWinner ): PlayCardAction
    {
        $availableCardsToPlayIterator = $context->AvailableCardsToPlay->getIterator();
        $availableCardsToPlayIterator->uasort( function ( $a, $b ) {
            return $a->NoTrumpOrder <=> $b->NoTrumpOrder;
        });
        $availableCards = new ArrayCollection( \iterator_to_array( $availableCardsToPlayIterator ) );
        
        return new PlayCardAction( $availableCards->first() ); // .Lowest(x => x.NoTrumpOrder)
    }
    
    public function PlayFourth( PlayerPlayCardContext $context, Collection $playedCards, PlayerPosition $trickWinner ): PlayCardAction
    {
        $cardsToPlay = $context->AvailableCardsToPlay->filter(
            function( $entry ) {
                return $entry->Type != CardType::Ace && $entry->Type != CardType::Ten;
            }
        );
        
        if ( PlayerPositionExtensions::IsInSameTeamWith( $trickWinner, $context->MyPosition ) && $cardsToPlay->count() ) {
            $availableCardsToPlayIterator = $context->AvailableCardsToPlay->getIterator();
            $availableCardsToPlayIterator->uasort( function ( $a, $b ) {
                return $a->NoTrumpOrder <=> $b->NoTrumpOrder;
            });
            $cardsToPlay = new ArrayCollection( \iterator_to_array( $availableCardsToPlayIterator ) );
                
            return new PlayCardAction(
                $cardsToPlay->filter(
                    function( $entry ) {
                        return $entry->Type != CardType::Ace && $entry->Type != CardType::Ten;
                    }
                )->last()
            ); // .Highest(x => x.NoTrumpOrder)
        }
        
        $availableCardsToPlayIterator = $context->AvailableCardsToPlay->getIterator();
        $availableCardsToPlayIterator->uasort( function ( $a, $b ) {
            return $a->NoTrumpOrder <=> $b->NoTrumpOrder;
        });
        $availableCards = new ArrayCollection( \iterator_to_array( $availableCardsToPlayIterator ) );
        
        return new PlayCardAction( $availableCards->first() ); // .Lowest(x => x.NoTrumpOrder)
    }
}
