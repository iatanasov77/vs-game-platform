<?php namespace App\Component\Websocket;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

/**
 * Manual:  https://stackoverflow.com/questions/64292868/how-to-send-a-message-to-specific-websocket-clients-with-symfony-ratchet
 *          https://stackoverflow.com/questions/30953610/how-to-send-messages-to-particular-users-ratchet-php-websocket
 */
class MessageHandler implements WampServerInterface
{
    /** @var \SplObjectStorage */
    protected $clients;
    
    /** @var array */
    protected $names;
    
    /** @var string */
    protected $logFile;
    
    
    
    
    
    
    
    /**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribedTopics = array();
    
    
    
    
    
    
    
    
    
    
    
    public function __construct()
    {
        //$this->clients  = new \SplObjectStorage;
        $this->clients  = [];
        $this->names    = [];
        $this->logFile  = '/var/log/websocket/game-patform.log';
    }
    
    
    
    
    
    
    
    
    public function onSubscribe( ConnectionInterface $conn, $topic )
    {
        $this->subscribedTopics[$topic->getId()] = $topic;
    }
    
    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onBlogEntry( $entry )
    {
        $entryData = \json_decode( $entry, true );
        
        // If the lookup topic object isn't set there is no one to publish to
        if ( ! \array_key_exists( $entryData['test'], $this->subscribedTopics ) ) {
            return;
        }
        
        $topic = $this->subscribedTopics[$entryData['test']];
        
        // re-send the data to all the clients subscribed to that category
        $topic->broadcast( $entryData );
    }
    
    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
    }
    public function onOpen(ConnectionInterface $conn) {
    }
    public function onClose(ConnectionInterface $conn) {
    }
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->close();
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
    
    
    
    
    
    
    
    
    
    
    
    /*
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
    */
    
    
    
    
    
    private function log( $logData ): void
    {
        \file_put_contents( $this->logFile, $logData, FILE_APPEND | LOCK_EX );
    }
}
