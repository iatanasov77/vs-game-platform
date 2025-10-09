<?php namespace App\Component\Rules\CardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Deck
{
    /** @var Collection | Card[] */
    private $listOfCards;
    
    /** @var int */
    private $currentCardIndex;
    
    public function __construct()
    {
        Card::instance();
        $this->listOfCards = Card::$AllCards;
    }
    
    public function Shuffle(): void
    {
        $entries = $this->listOfCards->toArray();
        shuffle( $entries );
        
        $this->listOfCards = new ArrayCollection( $entries );
        $this->currentCardIndex = 0;
    }
    
    public function GetNextCard(): Card
    {
        return $this->listOfCards[$this->currentCardIndex++];
    }
    
    public function Cards(): Collection
    {
        return $this->listOfCards;
    }
}
