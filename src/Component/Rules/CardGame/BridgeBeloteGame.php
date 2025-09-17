<?php namespace App\Component\Rules\CardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerPosition;

/**
 * BelotGameEngine in C#: https://github.com/NikolayIT/BelotGameEngine
 */
class BridgeBeloteGame extends Game
{
    /** @var Collection | BridgeBeloteDeclaration[] */
    public $Declarations;
    
    /** @var int */
    public $southNorthPoints;
    
    /** @var int */
    public $eastWestPoints;
    
    /** @var PlayerPosition */
    public $firstInRound;
    
    /** @var int */
    public $roundNumber;
    
    /** @var int */
    public $hangingPoints;
    
    public function SetStartPosition(): void
    {
        $this->playGame();
    }
    
    public function PlayGame( PlayerPosition $firstToPlay = PlayerPosition::South ): void
    {
        $this->southNorthPoints = 0;
        $this->eastWestPoints = 0;
        $this->firstInRound = $firstToPlay;
        $this->roundNumber = 1;
        $this->hangingPoints = 0;
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
}
