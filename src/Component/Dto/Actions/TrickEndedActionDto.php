<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\GameDto;
use App\Component\Dto\BridgeBeloteScoreDto;

class TrickEndedActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::trickEnded->value;
    }
    
    public GameDto $game;
    public BridgeBeloteScoreDto $newScore;
}
