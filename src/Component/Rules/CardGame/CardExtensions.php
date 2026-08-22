<?php namespace App\Component\Rules\CardGame;

use BitMask\EnumBitMask;
use App\Component\Type\CardSuit;
use App\Component\Type\BridgeBeloteCardType as CardType;
use App\Component\Type\BidTrump;

class CardExtensions
{
    private static $NoTrumpValues = [ 0, 0, 0, 10, 2, 3, 4, 11 ];
    private static $TrumpValues = [ 0, 0, 14, 10, 20, 3, 4, 11 ];
    
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
    
    public static function ToBidTrump( CardSuit $cardSuit ): BidTrump
    {
        if ( $cardSuit == CardSuit::Club ) {
            $bidType = BidTrump::Clubs;
        } elseif ( $cardSuit == CardSuit::Diamond ) {
            $bidType = BidTrump::Diamonds;
        } elseif ( $cardSuit == CardSuit::Heart ) {
            $bidType = BidTrump::Hearts;
        } elseif ( $cardSuit == CardSuit::Spade ) {
            $bidType = BidTrump::Spades;
        } else {
            $bidType = BidTrump::Pass;
        }
        
        return $bidType;
    }
    
    public static function GetValue( Card $card, EnumBitMask $contract ): int
    {
        if ( $contract->has( BidTrump::AllTrumps ) ) {
            return self::$TrumpValues[$card->Type->value];
        }
        
        if ( $contract->has( BidTrump::NoTrumps ) ) {
            return self::$NoTrumpValues[$card->Type->value];
        }
        
        if ( $contract->get() == BidTrump::Pass->bitMaskValue() ) {
            return 0;
        }
        
        $suit = BidTrump::fromBitMaskValue( $contract->get() );
        if ( BidTrumpExtensions::ToCardSuit( $suit ) == $card->Suit ) {
            return self::$TrumpValues[$card->Type->value];
        }
        
        return self::$NoTrumpValues[$card->Type->value];
    }
}
