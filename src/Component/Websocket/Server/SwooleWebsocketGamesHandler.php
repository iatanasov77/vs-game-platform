<?php namespace App\Component\Websocket\Server;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use SplObjectStorage as SplObjectStorageAlias;

use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebsocketServer;

use Vankosoft\WebsocketBundle\Websocket\WebSocketState;
use Vankosoft\WebsocketBundle\Websocket\Server\ServerHandler;
use Vankosoft\ApplicationBundle\Component\MyLoggerInterface;

use App\Component\GameVariant;
use App\Component\Websocket\WebsocketClientFactory;
use App\Component\GameService;
use App\Component\Type\PlayerColor;
use App\Component\Type\PlayerPosition;

use App\Component\Dto\Actions\TestMessageActionDto;
use App\Component\Dto\TestMessageDto;

class SwooleWebsocketGamesHandler extends ServerHandler
{
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
    
    public function __construct(
        MyLoggerInterface $logger,
        SerializerInterface $serializer,
        bool $logExceptionTrace,
        RepositoryInterface $usersRepository,
        WebsocketClientFactory $wsClientFactory,
        GameService $gameService
    ) {
        parent::__construct( $logger, $serializer, $logExceptionTrace );
        
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
    }
    
    public function onOpen( WebsocketServer $server, Request $request ): void
    {
        $this->logger->log( "Swoole Server Call Subscriber onOpened !!!" );
    }
    
    public function onMessage( WebsocketServer $server, Frame $frame ): void
    {
        $this->logger->log( "Swoole Server Call Subscriber onMessage: {$frame->data}" );
        
        $messageDto = new TestMessageDto();
        $messageDto->message = 'Respond of Test Websocket Swool Message !!!';
        
        $action = new TestMessageActionDto();
        $action->message = $messageDto;
        $json   = $this->serializer->serialize( $action, JsonEncoder::FORMAT );
        
        $server->push( $frame->fd, $json );
    }
    
    public function onClose( WebsocketServer $server, int $fd ): void
    {
        $this->logger->log( "Swoole Server Call Subscriber onClose !!!" );
    }
}
