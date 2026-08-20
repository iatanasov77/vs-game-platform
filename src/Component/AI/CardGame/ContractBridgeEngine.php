<?php namespace App\Component\AI\CardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\GameLogger;
use App\Component\Type\BidTrump;
use App\Component\Rules\CardGame\Game;

// Contexts
use App\Component\Rules\CardGame\Context\PlayerGetBidContext;
use App\Component\Rules\CardGame\Context\PlayerPlayCardContext;

class ContractBridgeEngine extends Engine
{
    public function __construct( GameLogger $logger, Game $game )
    {
        parent::__construct( $logger, $game );
    }
    
    public function DoBid(): BidTrump
    {
        $context = new PlayerGetBidContext();
        $context->MyPosition = $this->EngineGame->CurrentPlayer;
        $context->Bids = $this->EngineGame->Bids;
        $context->AvailableBids = $this->EngineGame->AvailableBids;
        $context->MyCards = $this->EngineGame->playerCards[$this->EngineGame->CurrentPlayer->value];
        
        return $this->GetBid( $context );
    }
    
    protected function _GenerateTricksSequence( Collection &$sequences, Collection &$tricks, Game $game ): void
    {
        
    }
    
    private function GetBid( PlayerGetBidContext $context ): BidTrump
    {
        $bid = BidTrump::Pass;
        
        return $bid;
    }
}
