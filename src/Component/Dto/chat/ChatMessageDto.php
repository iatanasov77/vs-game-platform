<?php namespace App\Component\Dto\chat;

class ChatMessageDto extends ChatDto
{
    public string $fromUser;
    public string $message;
    public string $utcDateTime;
}
