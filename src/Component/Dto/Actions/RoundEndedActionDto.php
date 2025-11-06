<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\GameDto;
use App\Component\Dto\BridgeBeloteScoreDto;

class RoundEndedActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::roundEnded->value;
    }
    
    public GameDto $game;
    public BridgeBeloteScoreDto $newScore;
    
    // Debug Tricks
    public array $SouthNorthTricks;
    public array $EastWestTricks;
}
