<?php namespace App\Component\Dto\Actions;

use App\Component\Type\PlayerColor;
use App\Component\Dto\GameDto;

class ChessGameStartedActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::chessGameStarted->value;
    }
    
    public ?PlayerColor $playerToMove;
    public int $moveTimer;
    
    public GameDto $game;
}
