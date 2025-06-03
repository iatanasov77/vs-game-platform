<?php namespace App\Component\Websocket\Server;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Psr\Log\LoggerInterface;

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * See Logs:        sudo tail -f /var/log/websocket/game-patform-server.log
 * Start Service:   sudo service websocket_game_platform restart
 * 
 * Manual:  https://stackoverflow.com/questions/64292868/how-to-send-a-message-to-specific-websocket-clients-with-symfony-ratchet
 *          https://stackoverflow.com/questions/30953610/how-to-send-messages-to-particular-users-ratchet-php-websocket
 */
class WebsocketMessageHandler implements MessageComponentInterface
{
    /** @var string */
    private $environement;
    
    /** @var SerializerInterface */
    private $serializer;
    
    /** @var LoggerInterface */
    private $logger;
    
    /** @var RepositoryInterface */
    private $usersRepository;
    
    /** @var \SplObjectStorage */
    private $clients;
    
    /** @var int */
    private $connectionSequenceId = 0;
    
    /** @var array */
    private $names;
    
    public function __construct(
        string $environement,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        RepositoryInterface $usersRepository
    ) {
        $this->environement     = $environement;
        $this->serializer       = $serializer;
        $this->logger           = $logger;
        $this->usersRepository  = $usersRepository;
        
        $this->clients  = new \SplObjectStorage();
        $this->names    = [];
    }
    
    public function onOpen( ConnectionInterface $conn )
    {
        $this->log( "New connection ({$conn->resourceId})" . date( 'Y/m/d h:i:sa' ) );
        $this->connectionSequenceId++;
        
        // Store the new connection to send messages to later
        $this->clients->attach( $conn, $this->connectionSequenceId );
        
        $this->names[$this->connectionSequenceId] = "Guest {$this->connectionSequenceId}";
    }
    
    public function onMessage( ConnectionInterface $from, $msg )
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
    
    public function onClose( ConnectionInterface $conn )
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->log( "Connection {$conn->resourceId} has disconnected" );
        
        /** @var int $sequenceId */
        $sequenceId = $this->clients[$conn];
        $this->clients->detach( $conn );
        
        // cleanup
        unset( $this->names[$sequenceId] );
    }
    
    public function onError( ConnectionInterface $conn, \Exception $e )
    {
        $this->log( "An error has occurred: {$e->getMessage()}\n" );
        $conn->close();
    }
    
    private function log( $logData ): void
    {
        if ( $this->environement == 'dev' ) {
            $this->logger->info( $logData );
        }
    }
}
