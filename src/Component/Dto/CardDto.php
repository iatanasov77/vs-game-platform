<?php namespace App\Component\Dto;

use App\Component\Type\CardSuit;
use App\Component\Type\BridgeBeloteCardType as CardType;
use App\Component\Type\PlayerPosition;

class CardDto
{
    public CardSuit $Suit;
    public CardType $Type;
    
    public PlayerPosition $position;
    public string $cardIndex;
    public bool $animate    = false;
}
