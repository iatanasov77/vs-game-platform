<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\ChessMoveDto;

class ChessMoveMadeActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::chessMoveMade->value;
    }
    
    /** @var ChessMoveDto $move */
    public ChessMoveDto $move;
}
