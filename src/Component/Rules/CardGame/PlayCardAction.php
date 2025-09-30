<?php namespace App\Component\Rules\CardGame;

use App\Component\Type\PlayerPosition;

class PlayCardAction
{
    public Card $Card;
    
    public bool $Belote;
    
    public PlayerPosition $Player;
    
    public int $TrickNumber;
    
    public function __construct( Card $card, bool $announceBeloteIfAvailable = true )
    {
        $this->Card = $card;
        $this->Belote = $announceBeloteIfAvailable;
    }
}
