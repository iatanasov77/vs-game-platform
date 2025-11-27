<?php namespace App\Component\AI\CardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ContractBridgeEngine extends Engine
{
    public function __construct( GameLogger $logger, Game $game )
    {
        parent::__construct( $logger, $game );
    }
    
    protected function _GenerateTricksSequence( Collection &$sequences, Collection &$tricks, Game $game ): void
    {
        
    }
}
