<?php namespace App\Component\Websocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * Manual:  https://stackoverflow.com/questions/64292868/how-to-send-a-message-to-specific-websocket-clients-with-symfony-ratchet
 *          https://stackoverflow.com/questions/30953610/how-to-send-messages-to-particular-users-ratchet-php-websocket
 */
class MessageHandler implements MessageComponentInterface
{
    /** @var \SplObjectStorage */
    protected $clients;
    
    /** @var array */
    protected $names;
    
    /** @var string */
    protected $logFile;
    
    public function __construct()
    {
        //$this->clients  = new \SplObjectStorage;
        $this->clients  = [];
        $this->names    = [];
        $this->logFile  = '/var/log/websocket/game-patform.log';
    }
    
    public function onOpen( ConnectionInterface $conn )
    {
        // Store the new connection to send messages to later
        $this->clients[$conn->resourceId] = $conn;
        
        $this->log( " \n" );
        $this->log( "New connection ({$conn->resourceId})" . date( 'Y/m/d h:i:sa' ) );
        $this->log( " \n" );
    }
    
    public function onMessage( ConnectionInterface $from, $msg )
    {
        $data = \json_decode( $msg );
        
        // The following line is for debugging purposes only
        $this->log( "   Incoming message: " . $msg . PHP_EOL );
        
        if ( isset( $data->username ) ) {
            // Register the name of the just connected user.
            if ( $data->username != '' ) {
                $this->names[$from->resourceId] = $data->username;
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
    }
    
    public function onClose( ConnectionInterface $conn )
    {
        // The connection is closed, remove it, as we can no longer send it messages
        //$this->clients->detach( $conn );
        unset( $this->clients[$conn->resourceId] );
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
}
