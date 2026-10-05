<?php namespace App\Component\WebsocketSwoole\Websocket;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use SplObjectStorage as SplObjectStorageAlias;

use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebsocketServer;

use App\Component\Logger\MyLoggerInterface;
use App\Component\GameVariant;
use App\Component\Websocket\WebsocketClientFactory;
use App\Component\Websocket\WebSocketState;
use App\Component\GameService;
use App\Component\Type\PlayerColor;
use App\Component\Type\PlayerPosition;

class ServerHandler implements ServerHandlerInterface
{
    /** @var MyLoggerInterface */
    private $logger;
    
    /** @var SerializerInterface */
    private $serializer;
    
    /** @var RepositoryInterface */
    private $usersRepository;
    
    /** @var WebsocketClientFactory */
    private $wsClientFactory;
    
    /** @var GameService */
    private $gameService;
    
    /** @var \SplObjectStorage */
    private $clients;
    
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
    
    /** @var bool */
    private $logExceptionTrace;
    
    public function __construct(
        MyLoggerInterface $logger,
        SerializerInterface $serializer,
        RepositoryInterface $usersRepository,
        WebsocketClientFactory $wsClientFactory,
        GameService $gameService,
        bool $logExceptionTrace
    ) {
        $this->logger           = $logger;
        $this->serializer       = $serializer;
        $this->usersRepository  = $usersRepository;
        $this->wsClientFactory  = $wsClientFactory;
        $this->gameService      = $gameService;
        
        $this->clients  = new SplObjectStorageAlias();
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
        
        $this->logExceptionTrace    = $logExceptionTrace;
    }
    
    public function onOpen( WebsocketServer $server, Request $request ): void
    {
        $this->logger->log( "Swoole Server Call Subscriber onOpened !!!" );
    }
    
    public function onMessage( WebsocketServer $server, Frame $frame ): void
    {
        $this->logger->log( "Swoole Server Call Subscriber onMessage !!!" );
    }
    
    public function onClose( WebsocketServer $server, int $fd ): void
    {
        $this->logger->log( "Swoole Server Call Subscriber onClose !!!" );
    }
}
