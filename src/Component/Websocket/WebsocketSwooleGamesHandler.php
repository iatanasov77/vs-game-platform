<?php namespace App\Component\Websocket;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Sylius\Component\Resource\Repository\RepositoryInterface;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebsocketServer;

use Vankosoft\WebsocketBundle\Websocket\WebsocketClientFactory;
use Vankosoft\WebsocketBundle\Websocket\WebSocketState;
use Vankosoft\WebsocketBundle\Websocket\Server\SwooleServerHandler;
use Vankosoft\ApplicationBundle\Component\MyLoggerInterface;

use App\Component\Logger\GameLogger;
use App\Component\GameVariant;
use App\Component\GameService;
use App\Component\Type\PlayerColor;
use App\Component\Type\PlayerPosition;

use App\Component\Dto\Actions\ActionDto;
use App\Component\Dto\Actions\ActionNames;

final class WebsocketSwooleGamesHandler extends SwooleServerHandler
{
    /** @var RepositoryInterface */
    private $usersRepository;
    
    /** @var GameService */
    private $gameService;
    
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
        $this->logger->log( "Swoole Server Open Connection: FD {$request->fd} Worker {$server->worker_id}", 'GameServer' );
        $this->connectionSequenceId++;
        
        // Store the new connection to send messages to later
        $this->clients->offsetSet( $server, $this->connectionSequenceId );
        
        // default nickname
        $this->names[$this->connectionSequenceId] = "Guest {$this->connectionSequenceId}";
        $this->ConnectGame( $server, $request );
        
        // $this->debugRequest( $request );
        // $this->debugGameManager( 'onOpen', $request->fd );
    }
    
    public function onRequest( Request $request, Response $response ): void
    {
        // Query string (e.g., price=high)
        $queryString = $request->server['query_string'] ?? '';
        $this->logger->log( "Swoole Server Request Query String: {$queryString}", 'GameServer' );
    }
    
    public function onMessage( WebsocketServer $server, Frame $frame ): void
    {
        $this->logger->log( "New message from ({$frame->fd}): {$frame->data}", 'GameServer' );
        
        if ( ! $this->clients->contains( $server ) ) {
            $this->logger->log( "Not Existing Game {$frame->fd} in WebsocketSwooleGamesHandler !!!", 'GameServer' );
            return;
        }
        
        $connectionSequenceId =  $this->clients->offsetGet( $server );
        
        if ( ! isset( $this->games[$connectionSequenceId] ) ) {
            $this->logger->log( "Not Existing Game {$frame->fd} in WebsocketSwooleGamesHandler !!!", 'GameServer' );
            return;
        }
        
        $gameManager    = $this->gameService->getGameManager( $this->games[$connectionSequenceId] );
        if ( ! $gameManager ) {
            $this->logger->log( "Not Existing Game Manager {$this->games[$connectionSequenceId]} in WebsocketSwooleGamesHandler !!!", 'GameServer' );
            return;
        }
        
        $socket         = $gameManager->getClient( $connectionSequenceId );
        if ( ! $socket ) {
            $this->logger->log( "Not Existing Client {$connectionSequenceId} in WebsocketSwooleGamesHandler !!!", 'GameServer' );
            return;
        }
        
        $action         = $this->serializer->deserialize( $frame->data, ActionDto::class, JsonEncoder::FORMAT );
        $this->logger->log( "Recieved Action: " . $action->actionName, 'GameServer' );
        
        try {
            $otherPlayers = [];
            $otherClients = [];
            switch ( $gameManager->GameCode ) {
                case GameVariant::BACKGAMMON_CODE:
                case GameVariant::CHESS_CODE:
                    $otherPlayers = $this->boardGamePlayers;
                    break;
            }
            
            if ( ( $key = \array_search( $gameManager->Game->CurrentPlayer->value, $otherPlayers ) ) !== false ) {
                unset( $otherPlayers[$key] );
            }
            
            foreach ( $otherPlayers as $player ) {
                $otherClients[] = $gameManager->Clients->get( $player );
            }
            
            $gameManager->DoAction( ActionNames::from( $action->actionName ), $frame->data, $socket, $otherClients );
        } catch ( \Exception $e ) {
            $this->logger->log( "Game Manager Do Action Error: '{$e->getMessage()}' in file {$e->getFile()} at line {$e->getLine()}", 'GameServer' );
            if ( $this->logExceptionTrace ) {
                $this->logger->log( "Exception Trace: {$e->getTraceAsString()}", 'GameServer' );
            }
        }
    }
    
    public function onClose( WebsocketServer $server, int $fd ): void
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->logger->log( "Swoole Close Connection: FD {$fd} Worker {$server->worker_id}", 'GameServer' );
        // $this->debugGameManager( 'onClose', $fd );
        
        if ( ! $this->clients->contains( $server ) ) {
            return;
        }
        
        $connectionSequenceId =  $this->clients->offsetGet( $server );
        
        $gameManager    = $this->gameService->getGameManager( $this->games[$connectionSequenceId] );
        $socket         = $gameManager->getClient( $connectionSequenceId );
        if ( ! $socket ) {
            return;
        }
        
        $socket->State  = WebSocketState::Closed;
        $this->clients->detach( $server );
        
        // cleanup
        unset( $this->names[$connectionSequenceId] );
    }
    
    private function ConnectGame( WebsocketServer $server, Request $request ): void
    {
        $this->logger->log( "Connect Game Request.", 'GameServer' );
        
        try {
            $queryString = $request->server['query_string'] ?? '';
            if ( empty( $queryString ) ) {
                return;
            }
            
            $queryParameters    = [];
            \parse_str( $queryString, $queryParameters );
            $this->logger->log( "API Verify Signature: ". $queryParameters['token'], 'GameServer' );
            
            $verifyToken    = isset( $queryParameters['token'] ) ? $queryParameters['token'] : null;
            $user           = $this->usersRepository->findOneBy( ['apiVerifySiganature' => $verifyToken ] );
            if ( ! $user ) {
                $this->logger->log( "User Not Found When Connecting Game.", 'GameServer' );
                return;
            }
            
            $gameCode   = isset( $queryParameters['gameCode'] ) ? $queryParameters['gameCode'] : null;
            $gameVariant    = isset( $queryParameters['gameVariant'] ) ? $queryParameters['gameVariant'] : null;
            if ( ! $gameCode ) {
                $this->logger->log( "Game Code Missing When Connecting Game.", 'GameServer' );
                return;
            }
            
            $webSocket  = $this->wsClientFactory->createClient( WebsocketClientFactory::SWOOLE_CLIENT, $this->websocketUrl, $server );
            $webSocket->setClientId( $this->connectionSequenceId );
            $webSocket->State   = WebSocketState::Open;
            
            $userId     = $user->getId();
            $gameId     = isset( $queryParameters['gameId'] ) ? $queryParameters['gameId'] : null;
            $playAi     = isset( $queryParameters['playAi'] ) ? $queryParameters['playAi'] : "false";
            $forGold    = isset( $queryParameters['forGold'] ) ? $queryParameters['forGold'] : "true";
            $gameCookie = isset( $queryParameters['gameCookie'] ) ? $queryParameters['gameCookie'] : null;
            
            if ( $gameCookie ) {
                $gameCookie = \base64_decode( $gameCookie );
                $this->logger->log( "Game Cookie: ". $gameCookie, 'GameServer' );
            }
            
            $this->logger->log( "Game Code: ". $gameCode, 'GameServer' );
            //$this->logger->log( "Game Id: ". $gameId, 'GameServer' );
            //$this->logger->log( "Play AI: ". $playAi, 'GameServer' );
            
            $gameGuid   = null;
            
            $gameGuid   = $this->gameService->Connect(
                $webSocket,
                $gameCode,
                $gameVariant,
                $userId,
                $gameId,
                $playAi === 'true',
                $forGold === 'true',
                $gameCookie
            );
        } catch ( \Exception $exc ) {
            $this->logger->log( "Connect Game Error: {$exc->getMessage()} in file {$exc->getFile()} at line {$exc->getLine()}", 'GameServer' );
            if ( $this->logExceptionTrace ) {
                $this->logger->log( "Exception Trace: {$exc->getTraceAsString()}", 'GameServer' );
            }
            
            return;
        }
        
        // $this->logger->log( "Swoole Connect Game: KEY: {$request->fd} GUID: {$gameGuid}", 'GameServer' );
        if ( $gameGuid ) {
            $this->logger->log( "Swoole Connect Game: SequenceId - {$this->connectionSequenceId} GameGuid - {$gameGuid}", 'GameServer' );
            
            $this->games[$this->connectionSequenceId] = $gameGuid;
        }
    }
    
    private function debugRequest( Request $request ): void
    {
        $this->logger->log( 'Websocket Request: ' . \print_r( $request, true ), 'GameServer' );
    }
    
    private function debugGameManager( string $prefix, int $resourceId ): void
    {
        $gameExist = isset( $this->games[$resourceId] );
        $this->logger->log( "{$prefix} - Game Manager Found: {$gameExist}", 'GameServer' );
    }
}
