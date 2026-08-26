<?php namespace App\Component\Dto\Actions;

use App\Component\Type\PlayerPosition;

class DummyFaceupActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::dummyFaceup->value;
    }
    
    public PlayerPosition $DummyPlayer;
    public PlayerPosition $Player;
}