<?php namespace App\Component\Dto\Actions;

class UndoActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::undoMove->value;
    }
}
