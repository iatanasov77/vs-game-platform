<?php namespace App\Component\Dto;

use App\Component\Type\CardSuit;
use App\Component\Type\BridgeBeloteCardType;
use App\Component\Type\ContractBridgeCardType;
use App\Component\Type\PlayerPosition;

class CardDto
{
    public CardSuit $Suit;
    
    /** @var BridgeBeloteCardType | ContractBridgeCardType */
    public $Type;
    
    public PlayerPosition $position;
    public string $cardIndex;
    public bool $animate    = false;
}
