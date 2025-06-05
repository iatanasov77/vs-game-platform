<?php namespace App\Component\Dto\Actions;

class HintMovesActionDto extends ActionDto
{
    public array $moves; // MoveDto[]
    
    public function  __construct()
    {
        $this->actionName = ActionNames::hintMoves->value;
    }
}
