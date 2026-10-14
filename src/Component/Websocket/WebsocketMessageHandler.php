<?php namespace App\Component\Websocket;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Sylius\Component\Resource\Repository\RepositoryInterface;

use Vankosoft\WebsocketBundle\Websocket\Connection\RatchetConnectionInterface;
use Vankosoft\WebsocketBundle\Websocket\Message\Frame;
use Vankosoft\WebsocketBundle\Websocket\Server\RatchetServerHandler;

/**
 * See Logs:        sudo tail -f /var/log/websocket/game-patform-server.log
 * Start Service:   sudo service websocket_game_platform restart
 * 
 * Manual:  https://stackoverflow.com/questions/64292868/how-to-send-a-message-to-specific-websocket-clients-with-symfony-ratchet
 *          https://stackoverflow.com/questions/30953610/how-to-send-messages-to-particular-users-ratchet-php-websocket
 */
class WebsocketMessageHandler extends RatchetServerHandler
{
    /** @var RepositoryInterface */
    private $usersRepository;
    
    /** @var GameService */
    private $gameService;
    
    /** @var array */
    private $names;
    
    /** @var array */
    private $games;
    
    public function __construct(
        GameLogger $logger,
        SerializerInterface $serializer,
        bool $logExceptionTrace,
        string $websocketUrl,
        WebsocketClientFactory $wsClientFactory,
        
        RepositoryInterface $usersRepository,
        GameService $gameService
    ) {
        parent::__construct( $logger, $serializer, $logExceptionTrace, $websocketUrl, $wsClientFactory );
        
        $this->usersRepository      = $usersRepository;
        $this->gameService          = $gameService;
        
        $this->names    = [];
        $this->games    = [];
    }
    
    public function onOpen( $conn )
    {
        $this->log( "New Ratchet connection ({$conn->resourceId})" . date( 'Y/m/d h:i:sa' ) );
        $this->connectionSequenceId++;
        
        // Store the new connection to send messages to later
        $this->clients->offsetSet( $conn, $this->connectionSequenceId );
        
        $this->names[$this->connectionSequenceId] = "Guest {$this->connectionSequenceId}";
    }
    
    public function onMessage( $from, $msg )
    {
        /** @var int $sequenceId */
        $sequenceId = $this->clients[$from];
        $data       = $this->serializer->deserialize( $msg, ActionDto::class, JsonEncoder::FORMAT );
        
        // The following line is for debugging purposes only
        $this->log( "Incoming message: " . $msg . PHP_EOL );
        
        if ( isset( $data->fromUser ) ) {
            // Register the name of the just connected user.
            if ( $data->fromUser != '' ) {
                $this->names[$from->resourceId] = $data->fromUser;
            }
        } else {
            if ( isset( $data->to ) ) {
                // The "to" field contains the name of the users the message should be sent to.
                if ( \str_contains( $data->to, ',' ) ) {
                    // It is a comma separated list of names.
                    $arrayUsers = \explode( ",", $data->to );
                    foreach( $arrayUsers as $name ) {
                        $key = \array_search( $name, $this->names );
                        if ( $key !== false ) {
                            $this->clients[$key]->send( $data->message );
                        }
                    }
                }
                else {
                    // Find a single user name in the $names array to get the key.
                    $key = \array_search ( $data->to, $this->names );
                    if ( $key !== false ) {
                        $this->clients[$key]->send( $data->message );
                    } else {
                        $this->log( "   User: " . $data->to . " not found" . PHP_EOL );
                    }
                }
            }
        }
        
        foreach ( $this->clients as $client ) {
//             if ( $from !== $client ) {
//                 $client->send( $sequenceId . $_ . $msg );
//             }
            
            $client->send( $msg );
        }
    }
    
    public function onClose( $conn )
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->log( "Connection {$conn->resourceId} has disconnected" );
        
        /** @var int $sequenceId */
        $sequenceId = $this->clients[$conn];
        $this->clients->detach( $conn );
        
        // cleanup
        unset( $this->names[$sequenceId] );
    }
    
    public function onError( $conn, \Exception $e )
    {
        $this->log( "An error has occurred: {$e->getMessage()}\n" );
        $conn->close( Frame::CLOSE_SRV_ERR );
    }
    
    private function log( $logData ): void
    {
        if ( $this->environement == 'dev' ) {
            $this->logger->info( $logData );
        }
    }
}
