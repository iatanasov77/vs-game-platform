<?php namespace App\Component\Rules\CardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Component\Type\CardSuit;
use App\Component\Type\CardType;

class Card
{
    use CardExtensions;
    
    /** @var Collection | Card[] */
    public static $AllCards;
    
    public static $AllSuits = [ CardSuit::Club, CardSuit::Diamond, CardSuit::Heart, CardSuit::Spade, ];
    
    public static $AllTypes = [
        CardType::Seven, CardType::Eight, CardType::Nine, CardType::Ten,
        CardType::Jack, CardType::Queen, CardType::King, CardType::Ace,
    ];
    
    private static $TrumpOrders = [ 1, 2, 7, 5, 8, 3, 4, 6 ];
    
    private static $NoTrumpOrders = [ 1, 2, 3, 7, 4, 5, 6, 8 ];
    
    private int $hashCode;
    
    public CardSuit $Suit;
    
    public CardType $Type;
    
    public int $TrumpOrder;
    
    public int $NoTrumpOrder;
    
    public static function instance()
    {
        self::$AllCards = new ArrayCollection();
        
        foreach ( self::$AllSuits as $suit )
        {
            foreach ( self::$AllTypes as $type )
            {
                $card = new Card( $suit, $type );
                self::$AllCards->set( $card->hashCode, $card );
            }
        }
    }
    
    public static function GetCard( CardSuit $suit, CardType $type ): Card
    {
        return self::$AllCards->get( ($suit->value * 8) + $type->value );
    }
    
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
    
    public function Type(): CardType
    {
        return $this->Type;
    }
    
    public function TrumpOrder(): int
    {
        return $this->TrumpOrder;
    }
    
    public function NoTrumpOrder(): int
    {
        return $this->NoTrumpOrder;
    }
    
    public function GetHashCode(): int
    {
        return $this->hashCode;
    }
    
    public function __toString(): string
    {
        return \sprintf(
            '%s%s',
            self::TypeToFriendlyString( $this->Type ),
            self::SuitToFriendlyString( $this->Suit )
        );
    }
    
    private function __construct( CardSuit $suit, CardType $type )
    {
        $this->hashCode = ( $suit->value * 8 ) + $type->value;
        $this->Suit = $suit;
        $this->Type = $type;
        $this->TrumpOrder = self::$TrumpOrders[$this->Type->value];
        $this->NoTrumpOrder = self::$NoTrumpOrders[$this->Type->value];
    }
}
