<?php namespace App\Component\Rules\CardGame;

use BitMask\EnumBitMask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;
use App\Component\Rules\CardGame\GameMechanics\RoundResult;

/**
 * BelotGameEngine in C#: https://github.com/NikolayIT/BelotGameEngine
 */
class BridgeBeloteGame extends Game
{
    /** @var int */
    public $southNorthPoints;
    
    /** @var int */
    public $eastWestPoints;
    
    /** @var int */
    public $hangingPoints;
    
    /** @var Collection | Announce[] */
    public $announces;
    
    public function PlayGame( PlayerPosition $firstToPlay = PlayerPosition::South ): void
    {
        parent::PlayGame( $firstToPlay );
        
        $this->southNorthPoints = 0;
        $this->eastWestPoints = 0;
        $this->hangingPoints = 0;
        $this->announces = new ArrayCollection();
    }
    
    public function NextPlayer(): PlayerPosition
    {
        switch ( $this->CurrentPlayer ) {
            case PlayerPosition::North:
                return PlayerPosition::West;
                break;
            case PlayerPosition::West:
                return PlayerPosition::South;
                break;
            case PlayerPosition::South:
                return PlayerPosition::East;
                break;
            case PlayerPosition::East:
                return PlayerPosition::North;
                break;
            default:
                throw new \RuntimeException( 'The Player Has No Position !' );
        }
    }
    
    public function IsBeloteAllowed( Collection $playerCards, EnumBitMask $contract, Collection $currentTrickActions, Card $playedCard ): bool
    {
        return $this->roundManager->IsBeloteAllowed( $playerCards, $contract, $currentTrickActions, $playedCard );
    }
    
    public function GetNewScore(): RoundResult
    {
        return $this->roundManager->GetScore(
            $this->CurrentContract,
            $this->SouthNorthTricks,
            $this->EastWestTricks,
            $this->announces,
            $this->hangingPoints,
            $this->LastTrickWinner
        );
    }
}
