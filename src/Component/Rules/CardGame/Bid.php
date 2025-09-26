<?php namespace App\Component\Rules\CardGame;

use BitMask\EnumBitMask;
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;

class Bid
{
    public PlayerPosition $Player;
    
    public EnumBitMask $Type;
    
    public function __construct( PlayerPosition $player, BidType $type )
    {
        $this->Player = $player;
        $this->Type = EnumBitMask::create( BidType::class, $type );
    }
    
    public function __toString(): string
    {
        return "{$this->Type->get()} ({$this->Player->value})";
    }
}
