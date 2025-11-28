<?php namespace App\Component\Rules\CardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Component\Type\CardSuit;

abstract class Card
{
    /** @var Collection | Card[] */
    public static $AllCards;
    
    public static $AllSuits = [ CardSuit::Club, CardSuit::Diamond, CardSuit::Heart, CardSuit::Spade, ];
    
    public CardSuit $Suit;
    
    protected int $hashCode;
    
    public static function Equals( Card $left, Card $right ): bool
    {
        return $left->hashCode == $right->hashCode;
    }
    
    public static function NotEquals( Card $left, Card $right ): bool
    {
        return ! ( $left->hashCode == $right->hashCode );
    }
    
    public function Suit(): CardSuit
    {
        return $this->Suit;
    }
    
    public function GetHashCode(): int
    {
        return $this->hashCode;
    }
    
    public function __toString(): string
    {
        return \sprintf(
            '%s%s',
            CardExtensions::TypeToFriendlyString( $this->Type ),
            CardExtensions::SuitToFriendlyString( $this->Suit )
        );
    }
}
