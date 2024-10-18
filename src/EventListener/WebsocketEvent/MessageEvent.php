<?php namespace App\EventListener\WebsocketEvent;

use App\Component\Manager\GameManagerInterface;
use App\Component\Websocket\Client\WebsocketClientInterface;

class MessageEvent
{
    public const NAME   = 'vs_game_platform.websocket_message_event';
    
    /** @var GameManagerInterface */
    private $manager;
    
    /** @var WebsocketClientInterface */
    private $socket;
    
    /** @var string */
    private $message;
    
    public function __construct(
        GameManagerInterface $manager,
        WebsocketClientInterface $socket,
        string $message
    ) {
        $this->manager  = $manager;
        $this->socket   = $socket;
        $this->message  = $message;
    }
    
    public function getGameManager()
    {
        return $this->manager;
    }
    
    public function getWebsocketClient()
    {
        return $this->socket;
    }
    
    public function getWebsocketMessage()
    {
        return $this->message;
    }
}
