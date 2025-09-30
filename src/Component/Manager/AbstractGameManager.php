<?php namespace App\Component\Manager;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Ratchet\RFC6455\Messaging\Frame;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager as LiipImagineCacheManager;
use React\Async;
use Amp\DeferredCancellation;
use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;

use App\Component\GameLogger;
use App\Component\Rules\GameInterface;

use App\Component\Rules\BoardGame\Game;
use App\Component\AI\AiEngineInterface;
use App\Component\Rules\GameFactory;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Websocket\WebSocketState;

// Types
use App\Component\Type\PlayerColor;
use App\Component\Type\GameState;

use App\Entity\GamePlayer;
use App\EventListener\Event\GameEndedEvent;

/**
 * See Logs:        sudo tail -f /dev/shm/game-platform.lh/game-platform/log/websocket.log
 */
abstract class AbstractGameManager implements GameManagerInterface
{
    /** @const string */
    const COLLECTION_ORDER_ASC  = 'ASC';
    
    /** @const string */
    const COLLECTION_ORDER_DESC = 'DESC';
    
    /** @const int */
    const firstBet = 50;
    
    /** @var GameLogger */
    protected $logger;
    
    /** @var SerializerInterface */
    protected $serializer;
    
    /** @var LiipImagineCacheManager */
    protected $imagineCacheManager;
    
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    
    /** @var ManagerRegistry */
    protected $doctrine;
    
    /** @var GameFactory */
    protected $gameRulesFactory;
    
    /** @var RepositoryInterface */
    protected $gameRepository;
    
    /** @var RepositoryInterface */
    protected $gamePlayRepository;
    
    /** @var FactoryInterface */
    protected $gamePlayFactory;
    
    /** @var RepositoryInterface */
    protected $playersRepository;
    
    /** @var FactoryInterface */
    protected $tempPlayersFactory;
    
    /**
     * CancellationTokenSource has been renamed to DeferredCancellation in AMPHP Version 3.0.0
     * 
     * @var DeferredCancellation
     */
    protected $moveTimeOut;
    
    /** @var bool */
    protected $EndGameOnTotalThinkTimeElapse;
    
    /** @var GameInterface */
    public $Game;
    
    /** @var AiEngineInterface | null */
    public $Engine = null;
    
    /** @var bool */
    public $SearchingOpponent;
    
    /** @var Collection | WebsocketClientInterface[] */
    public $Clients;
    
    /** @var string */
    public $Inviter;
    
    /** @var string */
    public $GameCode;
    
    /** @var string | null */
    public $GameVariant;
    
    /** @var bool */
    public $ForGold;
    
    /** @var \DateTime */
    public $Created;
    
    /** @var bool */
    public $RoomSelected = false;
    
    public function __construct(
        GameLogger $logger,
        SerializerInterface $serializer,
        LiipImagineCacheManager $imagineCacheManager,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        GameFactory $gameRulesFactory,
        RepositoryInterface $gameRepository,
        RepositoryInterface $gamePlayRepository,
        FactoryInterface $gamePlayFactory,
        RepositoryInterface $playersRepository,
        FactoryInterface $tempPlayersFactory,
        bool $forGold,
        string $gameCode,
        ?string $gameVariant,
        bool $EndGameOnTotalThinkTimeElapse
    ) {
        $this->logger                   = $logger;
        $this->serializer               = $serializer;
        $this->imagineCacheManager      = $imagineCacheManager;
        $this->eventDispatcher          = $eventDispatcher;
        $this->doctrine                 = $doctrine;
        $this->gameRulesFactory         = $gameRulesFactory;
        $this->gameRepository           = $gameRepository;
        $this->gamePlayRepository       = $gamePlayRepository;
        $this->gamePlayFactory          = $gamePlayFactory;
        $this->playersRepository        = $playersRepository;
        $this->tempPlayersFactory       = $tempPlayersFactory;
        $this->ForGold                  = $forGold;
        $this->GameCode                 = $gameCode;
        $this->GameVariant              = $gameVariant;
        $this->EndGameOnTotalThinkTimeElapse = $EndGameOnTotalThinkTimeElapse;
        $this->Clients                  = new ArrayCollection();
        
        // Initialize Game
        $this->Game = $this->gameRulesFactory->createGame( $gameCode, $gameVariant, $this->ForGold );
        $this->Game->ThinkStart = new \DateTime( 'now' );
        $this->Created          = new \DateTime( 'now' );
    }
    
    public function setLogger( LoggerInterface $logger ): void
    {
        $this->logger   = $logger;
    }
    
    public function getClient( $clientId ): ?WebsocketClientInterface
    {
        foreach ( $this->Clients as $client ) {
            if ( $client && $client->getClientId() == $clientId ) {
                $this->logger->log( 'Websocket Client Found !!!', 'GameManager' );
                return $client;
            }
        }
        
        $this->logger->log( 'GameManager Websocket Client Not Found !!!', 'GameManager' );
        return null;
    }
    
    public function getPlayerPhotoUrl( GamePlayer $player ): ? string
    {
        if ( $player->getUser() ) {
            $url    = $this->imagineCacheManager->getBrowserPath(
                $player->getUser()->getInfo()->getAvatar()->getPath(),
                'users_crud_index_thumb',
            );
            
            return \parse_url( $url, PHP_URL_PATH );
        } else {
            return $player->getPhotoUrl();
        }
    }
    
    public function dispatchGameEnded(): void
    {
        $this->eventDispatcher->dispatch( new GameEndedEvent( $this ), GameEndedEvent::NAME );
    }
    
    public function Send( ?WebsocketClientInterface $socket, object $obj ): void
    {
        //$this->logger->log( 'Game ' . print_r( $obj, true ), 'GameManager' );
        
        $sendObject = \get_class( $obj );
        $this->logger->log( "Sending {$sendObject}.", 'GameManager' );
        
        if ( ! $socket || $socket->State != WebSocketState::Open ) {
            $this->logger->log( "Cannot send to socket, connection was lost.", 'GameManager' );
            return;
        }
        
        // , [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT]
        $json = $this->serializer->serialize( $obj, JsonEncoder::FORMAT );
        $this->logger->log( "Sending to client {$json}", 'WebsocketSend' );
        
        try {
            $socket->send( $obj );
        } catch ( \Exception $exc ) {
            $this->logger->log( "Failed to send socket data. Exception: {$exc->getMessage()}", 'GameManager' );
        }
    }
    
    public static function GetJsonError()
    {
        switch ( \json_last_error() ) {
            case JSON_ERROR_NONE:
                return ' - No errors';
                break;
            case JSON_ERROR_DEPTH:
                return ' - Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                return ' - Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                return ' - Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                return ' - Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                return ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                return ' - Unknown error';
                break;
        }
    }
    
    protected function TimeTick(): void
    {
        if ( ! $this->moveTimeOut->isCancelled() ) {
            $ellapsed = ( new \DateTime( 'now' ) )->getTimestamp() - $this->Game->ThinkStart->getTimestamp();
            if ( $ellapsed > Game::TotalThinkTime ) {
                $CurrentPlayerColor = $this->Game->CurrentPlayer == PlayerColor::Black ? 'Black' : 'White';
                $this->logger->log( "The time run out for {$CurrentPlayerColor}", 'GameManager' );
                
                $this->moveTimeOut->cancel();
                $winner = $this->Game->CurrentPlayer == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
                $this->EndGame( $winner );
            }
        }
    }
    
    protected function Resign( PlayerColor $winner ): void
    {
        $this->EndGame( $winner );
        $this->logger->log( "{$winner} won Game {$this->Game->Id} by resignition.", 'GameManager' );
    }
    
    protected function EndGame( PlayerColor $winner )
    {
        $this->moveTimeOut->cancel();
        $this->Game->PlayState = GameState::ended;
        $this->logger->log( "The winner is {$winner->value}", 'EndGame' );
        
        $newScore = $this->SaveWinner( $winner );
        $this->SendWinner( $winner, $newScore );
    }
    
    protected function CloseConnections( WebsocketClientInterface $socket )
    {
        if ( $socket != null ) {
            $this->logger->log( "Closing client", 'ExitGame' );
            $socket->close( Frame::CLOSE_NORMAL );
            
            // Dispose Websocket
            if ( $socket == $this->Clients->get( PlayerColor::Black->value ) ) {
                $this->Clients->set( PlayerColor::Black->value, null );
            } else {
                $this->Clients->set( PlayerColor::White->value, null );
            }
        }
    }
    
    abstract protected function CreateDbGame(): void;
    
    abstract protected function IsAi( ?string $guid ): bool;
    
    abstract protected function NewTurn( WebsocketClientInterface $socket ): void;
    
    abstract protected function AisTurn(): bool;
}
