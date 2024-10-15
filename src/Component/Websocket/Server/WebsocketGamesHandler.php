<?php namespace App\Component\Websocket\Server;

use SplObjectStorage as SplObjectStorageAlias;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use App\Component\Websocket\WebsocketClientFactory;
use App\Component\Websocket\WebSocketState;
use App\Component\GameService;
use App\Component\Utils\Keys;
use App\EventListener\WebsocketEvent\MessageEvent;

final class WebsocketGamesHandler implements MessageComponentInterface
{
    const USER_LIST = 0;
    const USER_ID = 1;
    const USER_CONNECTED = 2;
    const USER_DISCONNECTED = 3;
    
    const DELIMITER = "|";
    
    /** @var RepositoryInterface */
    private $usersRepository;
    
    /** @var WebsocketClientFactory */
    private $wsClientFactory;
    
    /** @var GameService */
    private $gameService;
    
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    
    /** @var \SplObjectStorage */
    private $clients;
    
    /** @var int */
    private $connectionSequenceId = 0;
    
    /** @var array */
    private $names;
    
    /** @var array */
    private $games;
    
    /** @var string */
    private $logFile;
    
    public function __construct(
        RepositoryInterface $usersRepository,
        WebsocketClientFactory $wsClientFactory,
        GameService $gameService,
        EventDispatcherInterface $eventDispatcher,
    ) {
        $this->usersRepository  = $usersRepository;
        $this->wsClientFactory  = $wsClientFactory;
        $this->gameService      = $gameService;
        $this->eventDispatcher  = $eventDispatcher;
        
        $this->clients  = new SplObjectStorageAlias();
        $this->names    = [];
        $this->games    = [];
        
        $this->logFile  = '/var/log/websocket/game-patform-game.log';
    }
    
    public function onOpen( ConnectionInterface $conn )
    {
        $this->connectionSequenceId++;
        
        // Store the new connection to send messages to later
        $this->clients->attach( $conn, $this->connectionSequenceId );
        
        // default nickname
        $this->names[$this->connectionSequenceId] = "Guest {$this->connectionSequenceId}";
        
        // initialize the drawing state for the user as false
        //$this->drawing[$this->connectionSequenceId] = false;
        
        $this->log( "New connection ({$conn->resourceId})" . date( 'Y/m/d h:i:sa' ) );
        
        $cookieDto  = $this->getCookie( $conn );
        $this->ConnectGame( $conn, $cookieDto );
    }
    
    public function onMessage( ConnectionInterface $from, $msg )
    {
        $this->log( "New message from ({$from->resourceId}): " . $msg );
        
        $gameManager    = $this->gameService->getGameManager( $this->games[$from->resourceId] );
        $socket         = $gameManager->getClient( $from->resourceId );
        
        if ( $gameManager ) {
            $this->eventDispatcher->dispatch( new MessageEvent( $gameManager, $socket, $msg, $this->logFile ) );
        }
    }
    
    public function onClose( ConnectionInterface $conn )
    {
        // The connection is closed, remove it, as we can no longer send it messages
        
        $gameManager    = $this->gameService->getGameManager( $this->games[$conn->resourceId] );
        $socket         = $gameManager->getClient( $conn->resourceId );
        $socket->State  = WebSocketState::Closed;
        
        /** @var int $sequenceId */
        $sequenceId = $this->clients[$conn];
        $this->clients->detach( $conn );
        
        // cleanup
        unset( $this->names[$sequenceId] );
        
        $this->log( "Connection {$conn->resourceId} has disconnected" );
    }
    
    public function onError( ConnectionInterface $conn, \Exception $e )
    {
        $this->log( "An error has occurred: {$e->getMessage()}" );
        $conn->close();
    }
    
    private function log( $logData ): void
    {
        \file_put_contents( $this->logFile, $logData . "\n", FILE_APPEND | LOCK_EX );
    }
    
    private function getCookie( ConnectionInterface $conn ): ?string
    {
        $cookieDto      = null;
        $sessionCookies = $conn->httpRequest->getHeader( 'Cookie' );
        if ( ! empty( $sessionCookies ) ) {
            //$this->log( "All Cookies: ". $sessionCookies[0] );
            
            $cookiesArray   = \explode( '; ', $sessionCookies[0] );
            foreach( $cookiesArray as $cookie ) {
                if( \strpos( $cookie, Keys::GAME_ID_KEY ) == 0 ) {
                    $cookieDto  = \explode( '=', $cookie )[1];
                    break;
                }
            }
        }
        
        return $cookieDto;
    }
    
    private function ConnectGame( ConnectionInterface $conn, ?string $gameCookie ): void
    {
        $this->log( "New web socket request." );
        
        \parse_str( $conn->httpRequest->getUri()->getQuery(), $queryParameters );
        //$this->log( "API Verify Signature: ". $queryParameters['token'] );
        
        $user   = $this->usersRepository->findOneBy( ['apiVerifySiganature' => $queryParameters['token']] );
        if ( ! $user ) {
            return;
        }
        
        $gameCode   = isset( $queryParameters['gameCode'] ) ? $queryParameters['gameCode'] : null;
        if ( ! $gameCode ) {
            return;
        }
        
        $webSocket  = $this->wsClientFactory->createRatchetConnectionClient( $conn );
        $webSocket->State   = WebSocketState::Open;
        
        $userId     = $user->getId();
        $gameId     = isset( $queryParameters['gameId'] ) ? $queryParameters['gameId'] : null;
        $playAi     = isset( $queryParameters['playAi'] ) ? $queryParameters['playAi'] : "true";
        $forGold    = isset( $queryParameters['forGold'] ) ? $queryParameters['forGold'] : "true";
        
        //$this->log( "Game Code: ". $gameCode );
        //$this->log( "Game Id: ". $gameId );
        
        try {
            $gameGuid   = $this->gameService->Connect( $webSocket, $gameCode, $userId, $gameId, $playAi, $forGold, $gameCookie );
        } catch ( \Exception $exc ) {
            $this->log( $exc->getMessage() );
            //await context.Response.WriteAsync(exc.Message, CancellationToken.None);
            //context.Response.StatusCode = 400;
            
            return;
        }
        
        if ( $gameGuid ) {
            $this->games[$conn->resourceId]  = $gameGuid;
        }
    }
}
