<?php namespace App\Component\Rules\CardGame;

use BitMask\EnumBitMask;
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidTrump;

class Bid
{
    public PlayerPosition $Player;
    public ?PlayerPosition $KontraPlayer = NULL;
    public ?PlayerPosition $ReKontraPlayer = NULL;
    
    public EnumBitMask $Trump;
    
    public function __construct( PlayerPosition $player, BidTrump $type )
    {
        $this->Player = $player;
        $this->Trump = EnumBitMask::create( BidTrump::class, $type );
    }
    
    public function __toString(): string
    {
        return "{$this->Trump->get()} ({$this->Player->value})";
    }
}
