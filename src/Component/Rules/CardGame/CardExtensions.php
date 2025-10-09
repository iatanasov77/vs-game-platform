<?php namespace App\Component\Rules\CardGame;

use App\Component\Type\CardSuit;
use App\Component\Type\CardType;
use App\Component\Type\BidType;

class CardExtensions
{
    public static function SuitToString( CardSuit $cardSuit ): string
    {
        switch ( $cardSuit ) {
            case CardSuit::Club:
                return "Club";
                break;
            case CardSuit::Diamond:
                return "Diamond";
                break;
            case CardSuit::Heart:
                return "Heart";
                break;
            case CardSuit::Spade:
                return "Spade";
                break;
            default:
                throw new \RuntimeException( "Invalid card suit." );
        }
    }
    
    public static function TypeToString( CardType $cardType ): string
    {
        switch ( $cardType ) {
            case CardType::Seven:
                return "Seven";
                break;
            case CardType::Eight:
                return "Eight";
                break;
            case CardType::Nine:
                return "Nine";
                break;
            case CardType::Ten:
                return "Ten";
                break;
            case CardType::Jack:
                return "Jack";
                break;
            case CardType::Queen:
                return "Queen";
                break;
            case CardType::King:
                return "King";
                break;
            case CardType::Ace:
                return "Ace";
                break;
            default:
                throw new \RuntimeException( "Invalid card type." );
        }
    }
    
    public static function SuitToFriendlyString( CardSuit $cardSuit ): string
    {
        switch ( $cardSuit ) {
            case CardSuit::Club:
                return "\u2663"; // ♣
                break;
            case CardSuit::Diamond:
                return "\u2666"; // ♦
                break;
            case CardSuit::Heart:
                return "\u2665"; // ♥
                break;
            case CardSuit::Spade:
                return "\u2660"; // ♠
                break;
            default:
                throw new \RuntimeException( "Invalid card suit." );
        }
    }
    
    public static function TypeToFriendlyString( CardType $cardType ): string
    {
        switch ( $cardType ) {
            case CardType::Seven:
                return "7";
                break;
            case CardType::Eight:
                return "8";
                break;
            case CardType::Nine:
                return "9";
                break;
            case CardType::Ten:
                return "10";
                break;
            case CardType::Jack:
                return "J";
                break;
            case CardType::Queen:
                return "Q";
                break;
            case CardType::King:
                return "K";
                break;
            case CardType::Ace:
                return "A";
                break;
            default:
                throw new \RuntimeException( "Invalid card type." );
        }
    }
    
    public static function ToBidType( CardSuit $cardSuit ): BidType
    {
        if ( $cardSuit == CardSuit::Club ) {
            $bidType = BidType::Clubs;
        } elseif ( $cardSuit == CardSuit::Diamond ) {
            $bidType = BidType::Diamonds;
        } elseif ( $cardSuit == CardSuit::Heart ) {
            $bidType = BidType::Hearts;
        } elseif ( $cardSuit == CardSuit::Spade ) {
            $bidType = BidType::Spades;
        } else {
            $bidType = BidType::Pass;
        }
        
        return $bidType;
    }
}
