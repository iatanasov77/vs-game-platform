<?php namespace App\Component\Dto\Actions;

class StartNewGameActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::startNewGame->value;
    }
}
