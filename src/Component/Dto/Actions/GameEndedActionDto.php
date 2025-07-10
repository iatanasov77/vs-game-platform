<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\GameDto;
use App\Component\Dto\toplist\NewScoreDto;

class GameEndedActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::gameEnded->value;
    }
    
    public GameDto $game;
    public ?NewScoreDto $newScore;
}
