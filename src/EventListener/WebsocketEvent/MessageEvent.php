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
    
    /** @var string */
    private $logFile;
    
    public function __construct(
        GameManagerInterface $manager,
        WebsocketClientInterface $socket,
        string $message,
        ?string $logFile
    ) {
        $this->manager  = $manager;
        $this->socket   = $socket;
        $this->message  = $message;
        $this->logFile  = $logFile;
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
        return $this->action;
    }
    
    public function log( $logData ): void
    {
        if ( ! $this->logFile ) {
            return;
        }
        
        \file_put_contents( $this->logFile, $logData . "\n", FILE_APPEND | LOCK_EX );
    }
}
