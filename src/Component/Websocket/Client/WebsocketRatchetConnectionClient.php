<?php namespace App\Component\Websocket\Client;

use Symfony\Component\Serializer\SerializerInterface;
use Ratchet\ConnectionInterface;

final class WebsocketRatchetConnectionClient extends AbstractWebsocketClient
{
    /** @var ConnectionInterface */
    private $connection;
    
    public function __construct( string $websocketUrl, SerializerInterface $serializer, ConnectionInterface $connection )
    {
        parent::__construct( $websocketUrl, $serializer );
        
        $this->connection   = $connection;
        $this->clientId     = $connection->resourceId;
    }
    
    public function send( object $msg ): void
    {
        // Here Use: Ratchet\Client\WebSocket
        $json   = $json = $this->serializer->serialize( $msg, 'json' );
        $this->connection->send( $json );
    }
    
    public function receive(): string
    {
        return '';
    }
    
    public function close(): void
    {
        $this->connection->close();
    }
    
    public function subscribe( string $realm, string $topic, \Closure $callback ): void
    {
        
    }
}
