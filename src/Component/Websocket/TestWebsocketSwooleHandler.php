<?php namespace App\Component\Websocket;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

use Swoole\Table;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebsocketServer;

use Vankosoft\WebsocketBundle\Websocket\WebsocketClientFactory;
use Vankosoft\WebsocketBundle\Websocket\WebSocketState;
use Vankosoft\WebsocketBundle\Websocket\Client\WebsocketClientInterface;
use Vankosoft\WebsocketBundle\Websocket\Server\SwooleServerHandler;
use Vankosoft\ApplicationBundle\Component\MyLoggerInterface;

use App\Component\Dto\Actions\OpenSwooleConnectionActionDto;
use App\Component\Dto\Actions\TestMessageActionDto;
use App\Component\Dto\TestMessageDto;

final class TestWebsocketSwooleHandler extends SwooleServerHandler
{
    public function __construct(
        MyLoggerInterface $logger,
        SerializerInterface $serializer,
        bool $logExceptionTrace,
        string $websocketUrl,
        WebsocketClientFactory $wsClientFactory
    ) {
        parent::__construct( $logger, $serializer, $logExceptionTrace, $websocketUrl, $wsClientFactory );
    }
    
    public function onOpen( WebsocketServer $server, Request $request ): void
    {
        $this->logger->log( "Swoole Server Open Connection: {$request->fd}", 'GameServer' );
        $this->connectionSequenceId++;
        
        // Store the new connection to send messages to later
        $this->clients->offsetSet( $server, $this->connectionSequenceId );
        
        $this->socket   = $this->wsClientFactory->createClient( WebsocketClientFactory::SWOOLE_CLIENT, $this->websocketUrl, $server );
        $this->socket->setClientId( $this->connectionSequenceId );
        $this->socket->State   = WebSocketState::Open;
        
        $action = new OpenSwooleConnectionActionDto();
        $action->type = 'welcome';
        $action->fd = $request->fd;
        
        $this->socket->send( $action );
    }
    
    public function onMessage( WebsocketServer $server, Frame $frame ): void
    {
        $this->logger->log( "New message from ({$frame->fd}): {$frame->data}", 'GameServer' );
        
        $messageDto = new TestMessageDto();
        $messageDto->message = 'Respond of Test Websocket Swoole Message !!!';
        
        $action = new TestMessageActionDto();
        $action->message = $messageDto;
        
        $this->socket->send( $action );
    }
    
    public function onClose( WebsocketServer $server, int $fd ): void
    {
        $this->logger->log( "Swoole Server Close Connection: {$fd}" );
        
        $this->connections->del( $fd );
    }
}
