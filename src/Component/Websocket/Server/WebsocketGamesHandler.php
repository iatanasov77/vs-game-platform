<?php namespace App\Component\Websocket\Server;

use SplObjectStorage as SplObjectStorageAlias;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use App\Component\Utils\Keys;
use App\Component\Dto\GameCookieDto;

final class WebsocketGamesHandler implements MessageComponentInterface
{
    const USER_LIST = 0;
    const USER_ID = 1;
    const USER_CONNECTED = 2;
    const USER_DISCONNECTED = 3;
    
    const DELIMITER = "|";
    
    /** @var \SplObjectStorage */
    protected $clients;
    
    /** @var int */
    protected $connectionSequenceId = 0;
    
    /** @var array */
    protected $names;
    
    /** @var string */
    protected $logFile;
    
    public function __construct()
    {
        $this->clients  = new SplObjectStorageAlias();
        $this->names    = [];
        
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
        
        $this->log( " \n" );
        $this->log( "New connection ({$conn->resourceId})" . date( 'Y/m/d h:i:sa' ) );
        $this->log( " \n" );
        
        $cookieDto      = $this->getCookie( $conn );
        //$this->ConnectGame( logger, context );
    }
    
    public function onMessage( ConnectionInterface $from, $msg )
    {
        
    }
    
    public function onClose( ConnectionInterface $conn )
    {
        // The connection is closed, remove it, as we can no longer send it messages
        /** @var int $sequenceId */
        $sequenceId = $this->clients[$conn];
        //$this->onMessage( $conn, self::USER_DISCONNECTED . self::DELIMITER . $sequenceId . self::DELIMITER . $this->names[$sequenceId] );
        $this->clients->detach( $conn );
        
        // cleanup
        unset( $this->names[$sequenceId] );
        //unset( $this->drawing[$this->connectionSequenceId] );
        
        $this->log( "Connection {$conn->resourceId} has disconnected\n" );
    }
    
    public function onError( ConnectionInterface $conn, \Exception $e )
    {
        $this->log( "An error has occurred: {$e->getMessage()}\n" );
        $conn->close();
    }
    
    private function log( $logData ): void
    {
        \file_put_contents( $this->logFile, $logData, FILE_APPEND | LOCK_EX );
    }
    
    private function getCookie( ConnectionInterface $conn ): ?GameCookieDto
    {
        $cookieDto      = null;
        $sessionCookies = $conn->httpRequest->getHeader( 'Cookie' );
        if ( ! empty( $sessionCookies ) ) {
            $this->log( "All Cookies: ". $sessionCookies[0] );
            $cookiesArray   = \explode( '; ', $sessionCookies[0] );
            foreach( $cookiesArray as $cookie ) {
                if( \strpos( $cookie, Keys::GAME_ID_KEY ) == 0 ) {
                    $cookieDto  = GameCookieDto::TryParse( \explode( '=', $cookie )[1] );
                    break;
                }
            }
        }
        
        return $cookieDto;
    }
    
    /*
    private function ConnectGame( ILogger<GameManager> $logger, HttpContext context )
    {
        logger.LogInformation($"New web socket request.");
        
        var webSocket = await context.WebSockets.AcceptWebSocketAsync();
        var userId = context.Request.Query.FirstOrDefault(q => q.Key == "userId").Value;
        var gameId = context.Request.Query.FirstOrDefault(q => q.Key == "gameId").Value;
        var playAi = context.Request.Query.FirstOrDefault(q => q.Key == "playAi").Value == "true";
        var forGold = context.Request.Query.FirstOrDefault(q => q.Key == "forGold").Value == "true";
        try
        {
            await GamesService.Connect(webSocket, context, logger, userId, gameId, playAi, forGold);
        }
        catch (Exception exc)
        {
            logger.LogError(exc.ToString());
            await context.Response.WriteAsync(exc.Message, CancellationToken.None);
            context.Response.StatusCode = 400;
        }
    }
    */
}
