<?php namespace App\Component\Websocket;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

use Vankosoft\WebsocketBundle\Websocket\WebsocketClientFactory;
use Vankosoft\WebsocketBundle\Websocket\WebSocketState;
use Vankosoft\WebsocketBundle\Websocket\Server\RatchetServerHandler;
use Vankosoft\ApplicationBundle\Component\MyLoggerInterface;

use App\Component\Dto\Actions\TestMessageActionDto;
use App\Component\Dto\TestMessageDto;

final class TestWebsocketRatchetHandler extends RatchetServerHandler
{
    /** @var int */
    private $connectionSequenceId = 0;
    
    /** @var array */
    private $names;
    
    /** @var array */
    private $games;
    
    public function __construct(
        MyLoggerInterface $logger,
        SerializerInterface $serializer,
        bool $logExceptionTrace,
        string $websocketUrl,
        WebsocketClientFactory $wsClientFactory
    ) {
        parent::__construct( $logger, $serializer, $logExceptionTrace, $websocketUrl, $wsClientFactory );
        
        $this->names    = [];
        $this->games    = [];
    }
    
    public function onOpen( ConnectionInterface $conn ): void
    {
        $this->logger->log( "Ratchet Server Open Connection: {$conn->resourceId}", 'GameServer' );
        $this->connectionSequenceId++;
        
        // Store the new connection to send messages to later
        $this->clients->offsetSet( $conn, $this->connectionSequenceId );
        
        $webSocket  = $this->wsClientFactory->createClient( WebsocketClientFactory::RATCHET_CLIENT, $this->websocketUrl, $conn );
        $webSocket->setClientId( $conn->resourceId );
        $webSocket->State   = WebSocketState::Open;
        
        $this->games[$this->connectionSequenceId] = $webSocket;
    }
    
    public function onMessage( ConnectionInterface $from, $msg ): void
    {
        $this->logger->log( "New message from ({$from->resourceId}): {$msg}", 'GameServer' );
        
        $messageDto = new TestMessageDto();
        $messageDto->message = 'Respond of Test Websocket Ratchet Message !!!';
        
        $action = new TestMessageActionDto();
        $action->message = $messageDto;
        
        if ( $this->clients->contains( $from ) ) {
            $connectionSequenceId =  $this->clients->offsetGet( $from );
            $this->games[$connectionSequenceId]->send( $action );
            
            $this->logger->log( "New message Sent To: ({$from->resourceId})", 'GameServer' );
        }
    }
    
    public function onClose( ConnectionInterface $conn ): void
    {
        $this->logger->log( "Ratchet Server Close Connection: {$conn->resourceId}" );
    }
}
