<?php namespace App\Component\Dto\Actions;

class StartNewRoundActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::startNewRound->value;
    }
}
