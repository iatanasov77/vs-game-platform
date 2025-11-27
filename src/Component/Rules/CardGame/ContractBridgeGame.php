<?php namespace App\Component\Rules\CardGame;

use BitMask\EnumBitMask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;
use App\Component\Rules\CardGame\ConractBridgeGameMechanics\RoundManager;

/**
 * ContractBridgeGame Engine in Phython: https://github.com/lorserker/ben
 * ContractBridgeGame in C#: https://github.com/PatrykkMar/Bridget
 */
class ContractBridgeGame extends Game
{
    public function PlayGame( PlayerPosition $firstToPlay = PlayerPosition::South ): void
    {
        $this->contractBridgeRoundManager = new RoundManager( $this, $this->logger, $this->eventDispatcher );
        
        $this->firstInRound = $firstToPlay;
        $this->roundNumber = 1;
        $this->trickNumber = 1;
    }
    
    public function NextPlayer(): PlayerPosition
    {
        switch ( $this->CurrentPlayer ) {
            case PlayerPosition::North:
                return PlayerPosition::East;
                break;
            case PlayerPosition::West:
                return PlayerPosition::North;
                break;
            case PlayerPosition::South:
                return PlayerPosition::West;
                break;
            case PlayerPosition::East:
                return PlayerPosition::South;
                break;
            default:
                throw new \RuntimeException( 'The Player Has No Position !' );
        }
    }
}
