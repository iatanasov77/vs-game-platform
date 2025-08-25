<?php namespace App\Component\Rules\CardGame;

use App\Component\Type\PlayerPosition;

class BridgeBeloteGame extends Game
{
    public function SetStartPosition(): void
    {
        $this->DealCards( 5, $this->NorthPlayer );
        $this->DealCards( 5, $this->WestPlayer );
        $this->DealCards( 5, $this->SouthPlayer );
        $this->DealCards( 5, $this->EastPlayer );
    }
    
    public function DealCards( int $count, Player $player ): void
    {
        
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
