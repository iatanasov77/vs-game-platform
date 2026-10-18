<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\TestMessageDto;

class TestReopenConnectionActionDto extends ActionDto
{
    public function  __construct()
    {
        $this->actionName = ActionNames::testReopenConnection->value;
    }
}
