<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\ChessMoveDto;

class ChessOpponentMoveActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::chessOpponentMove->value;
    }
    
    /** @var ChessMoveDto $move */
    public ChessMoveDto $move;
}
