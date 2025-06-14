<?php namespace App\Component\Websocket\Server;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

use Sylius\Component\Resource\Repository\RepositoryInterface;
use SplObjectStorage as SplObjectStorageAlias;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

use App\Component\GameLogger;
use App\Component\Websocket\WebsocketClientFactory;
use App\Component\Websocket\WebSocketState;
use App\Component\GameService;

use App\Component\Dto\Actions\ActionDto;
use App\Component\Dto\Actions\ActionNames;

/**
 * See Logs:        sudo tail -f /var/log/websocket/game-patform-game.log
 *                  sudo tail -f /dev/shm/game-platform.lh/game-platform/log/dev.log | grep MyDebug
 * 
 * Start Service:   sudo service websocket_game_platform_game restart
 *                  sudo /projects/VS_GamePlatform/bin/websocket_server_game 8092
 *
 * Manual:  https://stackoverflow.com/questions/64292868/how-to-send-a-message-to-specific-websocket-clients-with-symfony-ratchet
 *          https://stackoverflow.com/questions/30953610/how-to-send-messages-to-particular-users-ratchet-php-websocket
 */
final class WebsocketGamesHandler implements MessageComponentInterface
{
    /** @var GameLogger */
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
    
    /** @var bool */
    private $logExceptionTrace;
    
    public function __construct(
        GameLogger $logger,
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
        
        $this->logExceptionTrace    = $logExceptionTrace;
    }
    
    public function onOpen( ConnectionInterface $conn )
    {
        $this->logger->log( "New connection ({$conn->resourceId})" . date( 'Y/m/d h:i:sa' ), 'GameServer' );
        $this->connectionSequenceId++;
        
        // Store the new connection to send messages to later
        $this->clients->attach( $conn, $this->connectionSequenceId );
        
        // default nickname
        $this->names[$this->connectionSequenceId] = "Guest {$this->connectionSequenceId}";
        $this->ConnectGame( $conn );
        
        // broadcast new user
        //$this->onMessage( $conn, "New Connection: " . $this->connectionSequenceId );
    }
    
    public function onMessage( ConnectionInterface $from, $msg )
    {
        $this->logger->log( "New message from ({$from->resourceId}): " . $msg, 'GameServer' );
        //$this->debugGameManager( $from->resourceId );
        
        if ( ! isset( $this->games[$from->resourceId] ) ) {
            $this->logger->log( "Not Existing Game Manager {$from->resourceId} in WebsocketGamesHandler !!!", 'GameServer' );
            return;
        }
        
        $gameManager    = $this->gameService->getGameManager( $this->games[$from->resourceId] );
        $socket         = $gameManager->getClient( $from->resourceId );
        
        $action         = $this->serializer->deserialize( $msg, ActionDto::class, JsonEncoder::FORMAT );
        $this->logger->log( "Recieved Action: " . $action->actionName, 'GameServer' );
        
        try {
            // For Debugging
            // $gameManager->DoAction( ActionNames::from( $action->actionName ), $msg, $socket, $socket );
            
            $otherClient    = $socket == $gameManager->Client1 ? $gameManager->Client2 : $gameManager->Client1;
            $gameManager->DoAction( ActionNames::from( $action->actionName ), $msg, $socket, $otherClient );
        } catch ( \Exception $e ) {
            $this->logger->log( "Game Manager Do Action Error: '{$e->getMessage()}'", 'GameServer' );
            if ( $this->logExceptionTrace ) {
                $this->logger->log( "Exception Trace: {$e->getTraceAsString()}", 'GameServer' );
            }
        }
    }
    
    public function onClose( ConnectionInterface $conn )
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->logger->log( "Connection {$conn->resourceId} has disconnected", 'GameServer' );
        
        $gameManager    = $this->gameService->getGameManager( $this->games[$conn->resourceId] );
        $socket         = $gameManager->getClient( $conn->resourceId );
        $socket->State  = WebSocketState::Closed;
        
        /** @var int $sequenceId */
        $sequenceId = $this->clients[$conn];
        $this->clients->detach( $conn );
        
        // cleanup
        unset( $this->names[$sequenceId] );
    }
    
    public function onError( ConnectionInterface $conn, \Exception $e )
    {
        $this->logger->log( "An error has occurred: {$e->getMessage()}", 'GameServer' );
        if ( $this->logExceptionTrace ) {
            $this->logger->log( "Exception Trace: {$e->getTraceAsString()}", 'GameServer' );
        }
        
        $conn->close();
    }
    
    public function serverWasTerminated()
    {
        $this->logger->log( "WebSocket Server Was Terminated", 'GameServer' );
        foreach ( $this->clients as $value ) {
            $connection = $this->clients->current(); // current object
            $assocKey = $this->clients->getInfo(); // return, if exists, associated with cur. obj. data; else NULL
            
            //$this->logger->log( "Connection: " . \print_r( $connection, true ), 'GameServer' );
            //$this->logger->log( "Connection Info: " . \print_r( $assocKey, true ), 'GameServer' );
        }
    }
    
    private function ConnectGame( ConnectionInterface &$conn ): void
    {
        $this->logger->log( "Connect Game Request.", 'GameServer' );
        
        $queryString        = $conn->httpRequest->getUri()->getQuery();
        $queryParameters    = [];
        
        \parse_str( $queryString, $queryParameters );
        //$this->logger->log( "API Verify Signature: ". $queryParameters['token'], 'GameServer' );
        
        $user   = $this->usersRepository->findOneBy( ['apiVerifySiganature' => $queryParameters['token']] );
        if ( ! $user ) {
            $this->logger->log( "User Not Found When Connecting Game.", 'GameServer' );
            return;
        }
        
        $gameCode   = isset( $queryParameters['gameCode'] ) ? $queryParameters['gameCode'] : null;
        if ( ! $gameCode ) {
            $this->logger->log( "Game Code Missing When Connecting Game.", 'GameServer' );
            return;
        }
        
        $webSocket  = $this->wsClientFactory->createRatchetConnectionClient( $conn );
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
        
        //$this->logger->log( "Game Code: ". $gameCode, 'GameServer' );
        //$this->logger->log( "Game Id: ". $gameId, 'GameServer' );
        //$this->logger->log( "Play AI: ". $playAi, 'GameServer' );
        
        $gameGuid   = null;
        try {
            $gameGuid   = $this->gameService->Connect(
                $webSocket,
                $gameCode,
                $userId,
                $gameId,
                $playAi === 'true',
                $forGold === 'true',
                $gameCookie
            );
        } catch ( \Exception $exc ) {
            $this->logger->log( "Connect Game Error: {$exc->getMessage()}", 'GameServer' );
            if ( $this->logExceptionTrace ) {
                $this->logger->log( "Exception Trace: {$exc->getTraceAsString()}", 'GameServer' );
            }
            
            //await context.Response.WriteAsync(exc.Message, CancellationToken.None);
            //context.Response.StatusCode = 400;
            
            return;
        }
        
        //$this->logger->log( "Game GUID: ". $gameGuid, 'GameServer' );
        if ( $gameGuid ) {
            $this->games[$conn->resourceId]  = $gameGuid;
        }
    }
    
    private function debugGameManager( int $resourceId ): void
    {
        $this->logger->log( 'Game Manager Found: ' . isset( $this->games[$resourceId] ), 'GameServer' );
    }
}
