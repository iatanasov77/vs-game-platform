<?php namespace App\Component\Rules\CardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Component\Type\BridgeBeloteCardType as CardType;
use App\Component\Type\CardSuit;

class BridgeBeloteCard extends Card
{
    public static $AllTypes = [
        CardType::Seven, CardType::Eight, CardType::Nine, CardType::Ten,
        CardType::Jack, CardType::Queen, CardType::King, CardType::Ace,
    ];
    
    public CardType $Type;
    
    public int $TrumpOrder;
    
    public int $NoTrumpOrder;
    
    private static $TrumpOrders = [ 1, 2, 7, 5, 8, 3, 4, 6 ];
    
    private static $NoTrumpOrders = [ 1, 2, 3, 7, 4, 5, 6, 8 ];
    
    public static function instance()
    {
        self::$AllCards = new ArrayCollection();
        
        foreach ( self::$AllSuits as $suit )
        {
            foreach ( self::$AllTypes as $type )
            {
                $card = new BridgeBeloteCard( $suit, $type );
                self::$AllCards->set( $card->hashCode, $card );
            }
        }
    }
    
    public static function GetCard( CardSuit $suit, CardType $type ): Card
    {
        return self::$AllCards->get( ( $suit->value * 8 ) + $type->value );
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
    
    private function __construct( CardSuit $suit, CardType $type )
    {
        $this->hashCode = ( $suit->value * 8 ) + $type->value;
        $this->Suit = $suit;
        $this->Type = $type;
        $this->TrumpOrder = self::$TrumpOrders[$this->Type->value];
        $this->NoTrumpOrder = self::$NoTrumpOrders[$this->Type->value];
    }
}