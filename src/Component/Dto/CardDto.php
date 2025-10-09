<?php namespace App\Component\Dto;

use App\Component\Type\CardSuit;
use App\Component\Type\CardType;
use App\Component\Type\PlayerPosition;

class CardDto
{
    public CardSuit $Suit;
    public CardType $Type;
    
    public PlayerPosition $position;
    public string $cardIndex;
}
