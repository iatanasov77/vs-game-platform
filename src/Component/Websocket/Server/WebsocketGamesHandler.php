<?php namespace App\Component\Websocket\Server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

final class WebsocketGamesHandler implements MessageComponentInterface
{
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
        $this->clients  = new \SplObjectStorage;
        //$this->clients  = [];
        $this->names    = [];
        
        $this->logFile  = '/var/log/websocket/game-patform-server.log';
    }
    
    public function onOpen( ConnectionInterface $conn )
    {
        
    }
    
    public function onMessage( ConnectionInterface $from, $msg )
    {
        
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
