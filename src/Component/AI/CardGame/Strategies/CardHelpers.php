<?php namespace App\Component\AI\CardGame\Strategies;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\BridgeBeloteCardType as CardType;
use App\Component\Rules\CardGame\Card;

class CardHelpers
{
    public static function GetCardThatSurelyWinsATrickInAllTrumps(
        Collection $availableCardsToPlay,
        Collection $playerCards,
        Collection $playedCards,
        int $cardsThreshold
    ): ?Card {
        foreach ( $availableCardsToPlay as $card ) {
            $playedCardsCount = $playedCards->filter(
                function( $entry ) use ( $card ) {
                    return $entry && $entry->Suit == $card->Suit;
                }
            )->count();
            
            $playerCardsCount = $playerCards->filter(
                function( $entry ) use ( $card ) {
                    return $entry && $entry->Suit == $card->Suit;
                }
            )->count();
                
            if ( $card->Type == CardType::Jack && $playedCardsCount + $playerCardsCount > $cardsThreshold ) {
                return $card;
            }
            
            if ( $card->Type == CardType::Nine
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Jack ) )
            ) {
                return $card;
            }
            
            if ( $card->Type == CardType::Ace
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Nine ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Jack ) )
            ) {
                return $card;
            }
            
            if ( $card->Type == CardType::Ten
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Nine ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Jack ) )
            ) {
                return $card;
            }
            
            if ( $card->Type == CardType::King
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ten ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Nine ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Jack ) )
            ) {
                return $card;
            }
            
            if ( $card->Type == CardType::Queen
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::King ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ten ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Nine ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Jack ) )
            ) {
                return $card;
            }
            
            if ( $card->Type == CardType::Eight
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Queen ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::King ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ten ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Nine))
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Jack ) )
            ) {
                return $card;
            }
            
            if ( $card->Type == CardType::Seven
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Eight ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Queen ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::King ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ten ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Nine ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Jack ) )
            ) {
                return $card;
            }
        }
        
        return null;
    }
    
    public static function GetCardThatSurelyWinsATrickInNoTrumps(
        Collection $availableCardsToPlay,
        Collection $playerCards,
        Collection $playedCards
    ): ?Card {
        foreach ( $availableCardsToPlay as $card ) {
            $playedCardsCount = $playedCards->filter(
                function( $entry ) use ( $card ) {
                    return $entry && $entry->Suit == $card->Suit;
                }
            )->count();
                
            $playerCardsCount = $playerCards->filter(
                function( $entry ) use ( $card ) {
                    return $entry && $entry->Suit == $card->Suit;
                }
            )->count();
            
            if ( $card->Type == CardType::Ace && $playedCardsCount + $playerCardsCount > 4 ) {
                return $card;
            }
            
            if ( $card->Type == CardType::Ten && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) ) ) {
                return $card;
            }
            
            if ( $card->Type == CardType::King
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ten ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
            ) {
                return $card;
            }
            
            if ( $card->Type == CardType::Queen
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::King ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ten ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
            ) {
                return $card;
            }
            
            if ( $card->Type == CardType::Jack
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Queen ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::King ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ten ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
            ) {
                return $card;
            }
            
            if ( $card->Type == CardType::Nine
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Jack ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Queen ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::King ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ten ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
            ) {
                return $card;
            }
            
            if ( $card->Type == CardType::Eight
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Nine ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Jack ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Queen ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::King ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ten ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
            ) {
                return $card;
            }
            
            if ( $card->Type == CardType::Seven
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Eight ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Nine ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Jack ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Queen ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::King ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ten ) )
                && $playedCards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
            ) {
                return $card;
            }
        }
        
        return null;
    }
}
