<?php namespace App\Component\Rules\CardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Component\GameVariant;

class Deck
{
    /** @var Collection | Card[] */
    private $listOfCards;
    
    /** @var int */
    private $currentCardIndex;
    
    public function __construct( string $gameCode )
    {
        switch ( $gameCode ) {
            case GameVariant::BRIDGE_BELOTE_CODE:
                BridgeBeloteCard::instance();
                $this->listOfCards = BridgeBeloteCard::$AllCards;
                break;
            case GameVariant::CONTRACT_BRIDGE_CODE:
                ContractBridgeCard::instance();
                $this->listOfCards = ContractBridgeCard::$AllCards;
                break;
            case GameVariant::SVARA_CODE:
                ContractBridgeCard::instance();
                $this->listOfCards = ContractBridgeCard::$AllCards;
                break;
            default:
                throw new \RuntimeException( 'Unknown Game Code !!!' );
        }
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
    
    public function RemoveCard( Card $card ): void
    {
        $this->listOfCards->removeElement( $card );
    }
    
    public function Cards(): Collection
    {
        return $this->listOfCards;
    }
}
