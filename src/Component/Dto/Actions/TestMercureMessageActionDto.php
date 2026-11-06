<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\TestMessageDto;

class TestMercureMessageActionDto extends MercureActionDto
{
    public function  __construct()
    {
        $this->actionName = MercureActionNames::testMessage->value;
    }
    
    public TestMessageDto $message;
}
