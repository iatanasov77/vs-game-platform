<?php namespace App\Component\Dto\Actions;

class GamePlayStartedActionDto extends ActionDto
{
    public function  __construct()
    {
        $this->actionName = ActionNames::gamePlayStarted->value;
    }
}
