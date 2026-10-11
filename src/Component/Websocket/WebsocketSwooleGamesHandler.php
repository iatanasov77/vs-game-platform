<?php namespace App\Component\Websocket;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Sylius\Component\Resource\Repository\RepositoryInterface;

use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebsocketServer;

use Vankosoft\WebsocketBundle\Websocket\WebsocketClientFactory;
use Vankosoft\WebsocketBundle\Websocket\WebSocketState;
use Vankosoft\WebsocketBundle\Websocket\Server\SwooleServerHandler;
use Vankosoft\ApplicationBundle\Component\MyLoggerInterface;

use App\Component\GameVariant;
use App\Component\GameService;
use App\Component\Type\PlayerColor;
use App\Component\Type\PlayerPosition;

use App\Component\Dto\Actions\TestMessageActionDto;
use App\Component\Dto\TestMessageDto;

final class WebsocketSwooleGamesHandler extends SwooleServerHandler
{
    /** @var RepositoryInterface */
    private $usersRepository;
    
    /** @var GameService */
    private $gameService;
    
    /** @var int */
    private $connectionSequenceId = 0;
    
    /** @var array */
    private $names;
    
    /** @var array */
    private $games;
    
    /** @var array */
    private $boardGamePlayers;
    
    /** @var array */
    private $cardGamePlayers;
    
    public function __construct(
        MyLoggerInterface $logger,
        SerializerInterface $serializer,
        bool $logExceptionTrace,
        string $websocketUrl,
        WebsocketClientFactory $wsClientFactory,
        
        RepositoryInterface $usersRepository,
        GameService $gameService
    ) {
        parent::__construct( $logger, $serializer, $logExceptionTrace, $websocketUrl, $wsClientFactory );
        
        $this->usersRepository  = $usersRepository;
        $this->gameService      = $gameService;
        
        $this->names    = [];
        $this->games    = [];
        
        $this->boardGamePlayers = [
            PlayerColor::Black->value,
            PlayerColor::White->value
        ];
        
        $this->cardGamePlayers = [
            PlayerPosition::South->value,
            PlayerPosition::North->value,
            PlayerPosition::East->value,
            PlayerPosition::West->value
        ];
    }
    
    public function onOpen( WebsocketServer $server, Request $request ): void
    {
        $this->logger->log( "Swoole Server Open Connection: {$request->fd}", 'GameServer' );
        $this->connectionSequenceId++;
        
        // Store the new connection to send messages to later
        $this->clients->offsetSet( $server, $this->connectionSequenceId );
        
        $webSocket  = $this->wsClientFactory->createClient( WebsocketClientFactory::SWOOLE_CLIENT, $this->websocketUrl, $server );
        $webSocket->setClientId( $request->fd );
        $webSocket->State   = WebSocketState::Open;
        
        $this->games[$this->connectionSequenceId] = $webSocket;
    }
    
    public function onMessage( WebsocketServer $server, Frame $frame ): void
    {
        $this->logger->log( "New message from ({$frame->fd}): {$frame->data}", 'GameServer' );
        
        $messageDto = new TestMessageDto();
        $messageDto->message = 'Respond of Test Websocket Swool Message !!!';
        
        $action = new TestMessageActionDto();
        $action->message = $messageDto;
        // $json   = $this->serializer->serialize( $action, JsonEncoder::FORMAT );
        
        // $server->push( $frame->fd, $json );
        if ( $this->clients->contains( $server ) ) {
            $connectionSequenceId =  $this->clients->offsetGet( $server );
            $this->games[$connectionSequenceId]->send( $action );
            
            $this->logger->log( "New message Sent To: ({$frame->fd})", 'GameServer' );
        }
    }
    
    public function onClose( WebsocketServer $server, int $fd ): void
    {
        $this->logger->log( "Swoole Server Close Connection: {$fd}" );
    }
}
