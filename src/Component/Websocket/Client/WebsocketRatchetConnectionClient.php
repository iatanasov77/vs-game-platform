<?php namespace App\Component\Websocket\Client;

use Ratchet\ConnectionInterface;

final class WebsocketRatchetConnectionClient extends AbstractWebsocketClient
{
    /** @var ConnectionInterface */
    private $connection;
    
    public function __construct( string $websocketUrl, ConnectionInterface $connection )
    {
        parent::__construct( $websocketUrl );
        
        $this->connection   = $connection;
    }
    
    public function send( object $msg ): void
    {
        // Here Use: Ratchet\Client\WebSocket
        $this->connection->send( $msg );
    }
    
    public function receive(): string
    {
        return '';
    }
    
    public function subscribe( string $realm, string $topic, \Closure $callback ): void
    {
        
    }
}
