<?php namespace App\Component\Rules\CardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Component\Type\ContractBridgeCardType as CardType;
use App\Component\Type\CardSuit;

class ContractBridgeCard extends Card
{
    public static $AllTypes = [
        CardType::Two, CardType::Three, CardType::Four, CardType::Five, CardType::Six,
        CardType::Seven, CardType::Eight, CardType::Nine, CardType::Ten,
        CardType::Jack, CardType::Queen, CardType::King, CardType::Ace,
    ];
    
    public CardType $Type;
    
    public static function instance()
    {
        self::$AllCards = new ArrayCollection();
        
        foreach ( self::$AllSuits as $suit )
        {
            foreach ( self::$AllTypes as $type )
            {
                $card = new ContractBridgeCard( $suit, $type );
                self::$AllCards->set( $card->hashCode, $card );
            }
        }
    }
    
    public static function GetCard( CardSuit $suit, CardType $type ): Card
    {
        return self::$AllCards->get( ( $suit->value * 13 ) + $type->value );
    }
    
    public function Type(): CardType
    {
        return $this->Type;
    }
    
    private function __construct( CardSuit $suit, CardType $type )
    {
        $this->hashCode = ( $suit->value * 13 ) + $type->value;
        $this->Suit = $suit;
        $this->Type = $type;
    }
}