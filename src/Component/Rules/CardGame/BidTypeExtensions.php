<?php namespace App\Component\Rules\CardGame;

use App\Component\Type\BidType;
use App\Component\Type\CardSuit;

class BidTypeExtensions
{
    public static function ToCardSuit( BidType $bidType ): CardSuit
    {
        switch ( $bidType ) {
            case BidType::Clubs:
                return CardSuit::Club;
                break;
            case BidType::Diamonds:
                return CardSuit::Diamond;
                break;
            case BidType::Hearts:
                return CardSuit::Heart;
                break;
            case BidType::Spades:
                return CardSuit::Spade;
                break;
            default:
                throw new \RuntimeException( 'BidTypeExtensions Error' );
        }
    }
}
