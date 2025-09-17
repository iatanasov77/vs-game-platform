<?php namespace App\Component\Rules\CardGame;

class Deck
{
    /** @var array | Card[] */
    private $listOfCards;
    
    /** @var int */
    private $currentCardIndex;
    
    public function __construct()
    {
        Card::instance();
        $this->listOfCards = Card::$AllCards->toArray();
    }
    
    public function Shuffle(): void
    {
        shuffle( $this->listOfCards );
        $this->currentCardIndex = 0;
    }
    
    public function GetNextCard(): Card
    {
        return $this->listOfCards[$this->currentCardIndex++];
    }
    
    public function Cards(): array
    {
        return $this->listOfCards;
    }
}
