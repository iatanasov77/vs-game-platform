<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\GameDto;
use App\Component\Dto\ScoreDto;

class RoundEndedActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::roundEnded->value;
    }
    
    public GameDto $game;
    public ScoreDto $newScore;
    
    // Debug Tricks
    public array $SouthNorthTricks;
    public array $EastWestTricks;
}
