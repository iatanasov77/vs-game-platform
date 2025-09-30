<?php namespace App\Component\Manager;

// Types
use App\Component\Type\CardGameTeam;
use App\Component\Type\PlayerPosition;
use App\Component\Type\GameState;

// DTO Actions
use App\Component\Dto\Actions\BidMadeActionDto;

abstract class CardGameManager extends AbstractGameManager
{
    abstract protected function DoBid( BidMadeActionDto $action ): void;
    
    protected function SaveWinner( CardGameTeam $team ): ?array
    {
        
    }
    
    protected function GetWinner(): ?CardGameTeam
    {
        $winner = null;
        
        
        return $winner;
    }
    
    protected function SendWinner( CardGameTeam $team, ?array $newScore ): void
    {
        
    }
}
