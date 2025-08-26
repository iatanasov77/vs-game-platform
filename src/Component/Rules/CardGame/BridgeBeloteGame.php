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
    
    public function SetStartPosition(): void
    {
        $this->DealCards( 5, $this->NorthPlayer );
        $this->DealCards( 5, $this->WestPlayer );
        $this->DealCards( 5, $this->SouthPlayer );
        $this->DealCards( 5, $this->EastPlayer );
    }
    
    public function DealCards( int $count, Player $player ): void
    {
        if ( ! isset( $this->playerCards[$player->PlayerPosition->toString()] ) ) {
            $this->playerCards[$player->PlayerPosition->toString()] = [];
        }
        
        for ( $i = 0; $i < $count; $i++ ) {
            $this->playerCards[$player->PlayerPosition->toString()][] = array_shift( $this->cardDeck );
        }
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
