<?php namespace App\Component\ServerSentEvent\Message;

class AddAiPlayerMessage
{
    /** @var string */
    private $lobbyId;
    
    public function __construct( string $lobbyId )
    {
        $this->lobbyId = $lobbyId;
    }
    
    public function getLobbyId(): string
    {
        return $this->lobbyId;
    }
}
