<?php namespace App\Component\AI\CardGame\Strategies;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerPosition;
use App\Component\Type\CardType;
use App\Component\Rules\CardGame\Context\PlayerPlayCardContext;
use App\Component\Rules\CardGame\PlayCardAction;
use App\Component\Rules\CardGame\Card;
use App\Component\Rules\CardGame\CardExtensions;

class AllTrumpsTheirsContractStrategy implements IPlayStrategy
{
    public function PlayFirst( PlayerPlayCardContext $context, Collection $playedCards ): PlayCardAction
    {
        // Play card if it will surely win the trick
        $card = CardHelpers::GetCardThatSurelyWinsATrickInAllTrumps(
            $context->AvailableCardsToPlay,
            $context->MyCards,
            $playedCards,
            1
        );
        if ( $card != null ) {
            return new PlayCardAction( $card );
        }
        
        // Play card of the same suit as one of my teammate's bids
        $teammate = $context->MyPosition->GetTeammate();
        for ( $i = 0; $i < \count( Card::AllSuits ); $i++ ) {
            $cardSuit = Card::AllSuits[$i];
            $hasTeammateBid = ! $context->Bids->filter(
                function( $entry ) use ( $teammate, $cardSuit ) {
                    return $entry && $entry->Player == $teammate && $entry->Type == CardExtensions::ToBidType( $cardSuit );
                }
            )->isEmpty();
            $hasTeammateBidCard = ! $context->AvailableCardsToPlay->filter(
                function( $entry ) use ( $cardSuit ) {
                    return $entry && $entry->Suit == $cardSuit;
                }
            )->isEmpty();
            if ( $hasTeammateBid && $hasTeammateBidCard ) {
                $availableCardsToPlayIterator = $context->AvailableCardsToPlay->getIterator();
                $cardToPlay = $availableCardsToPlayIterator->uasort( function ( $a, $b ) {
                    return $a->TrumpOrder <=> $b->TrumpOrder;
                })->first();
                
                return new PlayCardAction( $cardToPlay ); // .Lowest(x => x.TrumpOrder)
            }
        }
        
        for ( $i = 0; $i < \count( Card::AllSuits ); $i++ ) {
            $cardSuit = Card.AllSuits[$i];
            if ( $context.AvailableCardsToPlay->ontains( Card::GetCard( $cardSuit, CardType::Queen ) )
                && $context.AvailableCardsToPlay->Contains( Card::GetCard( $cardSuit, CardType::King ) )
            ) {
                return new PlayCardAction( Card::GetCard( $cardSuit, CardType::Queen ), true );
            }
        }
        
        return new PlayCardAction(
            $context->AvailableCardsToPlay->first() // .Lowest(x => x.TrumpOrder)
        );
    }
    
    public function PlaySecond( PlayerPlayCardContext $context, Collection $playedCards ): PlayCardAction
    {
        $firstCardSuit = $context->CurrentTrickActions[0]->Card->Suit;
        if ( $context->AvailableCardsToPlay->contains( Card::GetCard( $firstCardSuit, CardType::Jack ) ) ) {
            return new PlayCardAction( Card::GetCard( $firstCardSuit, CardType::Jack ) );
        }
        
        if ( $context->AvailableCardsToPlay->contains( Card::GetCard( $firstCardSuit, CardType::Nine ) )
            && $playedCards->contains( Card::GetCard( $firstCardSuit, CardType::Jack ) )
        ) {
            return new PlayCardAction( Card::GetCard( $firstCardSuit, CardType::Nine ) );
        }
        
        if ( $context->AvailableCardsToPlay->contains( Card::GetCard( $firstCardSuit, CardType::Ace ) )
            && $playedCards->contains( Card::GetCard( $firstCardSuit, CardType::Nine ) )
            && $playedCards->contains( Card::GetCard( $firstCardSuit, CardType::Jack ) )
        ) {
            return new PlayCardAction( Card::GetCard( $firstCardSuit, CardType::Ace ) );
        }
        
        if ( $context->AvailableCardsToPlay->contains( Card::GetCard( $firstCardSuit, CardType::Ten ) )
            && $playedCards->contains( Card::GetCard( $firstCardSuit, CardType::Ace ) )
            && $playedCards->contains( Card::GetCard( $firstCardSuit, CardType::Nine ) )
            && $playedCards->contains( Card::GetCard( $firstCardSuit, CardType::Jack ) )
        ) {
            return new PlayCardAction( Card::GetCard( $firstCardSuit, CardType::Ten ) );
        }
        
        if ( $context->AvailableCardsToPlay->contains( Card::GetCard( $firstCardSuit, CardType::King ) )
            && $playedCards->contains( Card::GetCard( $firstCardSuit, CardType::Ten ) )
            && $playedCards->contains( Card::GetCard( $firstCardSuit, CardType::Ace ) )
            && $playedCards->contains( Card::GetCard( $firstCardSuit, CardType::Nine ) )
            && $playedCards->contains( Card::GetCard( $firstCardSuit, CardType::Jack ) )
        ) {
            return new PlayCardAction( Card::GetCard( $firstCardSuit, CardType::King ) );
        }
        
        if ( $context->AvailableCardsToPlay->contains( Card::GetCard( $firstCardSuit, CardType::Queen ) )
            && $playedCards->contains( Card::GetCard( $firstCardSuit, CardType::King ) )
            && $playedCards->contains( Card::GetCard( $firstCardSuit, CardType::Ten ) )
            && $playedCards->contains( Card::GetCard( $firstCardSuit, CardType::Ace ) )
            && $playedCards->contains( Card::GetCard( $firstCardSuit, CardType::Nine ) )
            && $playedCards->contains( Card::GetCard( $firstCardSuit, CardType::Jack ) )
        ) {
            return new PlayCardAction( Card::GetCard( $firstCardSuit, CardType::Queen ) );
        }
        
        if ( $context->AvailableCardsToPlay->contains( Card::GetCard( $firstCardSuit, CardType::Queen ) )
            && $context->AvailableCardsToPlay->contains( Card::GetCard( $firstCardSuit, CardType::King ) )
        ) {
            return new PlayCardAction( Card::GetCard( $firstCardSuit, CardType::Queen ), true );
        }
        
        return new PlayCardAction( $context->AvailableCardsToPlay->first() ); // .Lowest(x => x.TrumpOrder )
    }
    
    public function PlayThird( PlayerPlayCardContext $context, Collection $playedCards, PlayerPosition $trickWinner ): PlayCardAction
    {
        return $this->PlaySecond( $context, $playedCards );
    }
    
    public function PlayFourth( PlayerPlayCardContext $context, Collection $playedCards, PlayerPosition $trickWinner ): PlayCardAction
    {
        return $this->PlaySecond( $context, $playedCards );
    }
}
