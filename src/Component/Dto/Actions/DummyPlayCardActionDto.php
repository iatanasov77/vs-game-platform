<?php namespace App\Component\Dto\Actions;

class DummyPlayCardActionDto extends PlayCardActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::dummyPlayCard->value;
    }
}
