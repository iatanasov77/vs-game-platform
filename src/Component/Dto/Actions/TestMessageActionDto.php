<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\TestMessageDto;

class TestMessageActionDto extends ActionDto
{
    public function  __construct()
    {
        $this->actionName = ActionNames::testMessage->value;
    }
    
    public TestMessageDto $message;
}
