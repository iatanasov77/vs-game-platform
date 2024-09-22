<?php namespace App\Component\Websocket;

use Amp\Loop;
use Amp\Websocket\Client;

/**
 * WebsocketClient Based on AMPHP
 * ==============================
 * Reference: https://amphp.org/amp
 * Manual:  https://stackoverflow.com/questions/64292868/how-to-send-a-message-to-specific-websocket-clients-with-symfony-ratchet
 *          https://stackoverflow.com/questions/60780643/get-websocket-pings-from-an-open-stream-connection-using-amp-websocket
 */
final class WebsocketClient
{
    /** @var string */
    private $websocketUrl;
    
    /** @var WebSocketState */
    public $State;
    
    public function __construct( string $websocketUrl )
    {
        $this->websocketUrl = $websocketUrl;
        $this->State        = WebSocketState::None;
    }
    
    public function receive(): object
    {
        Loop::run(
            function () {
                $connection = yield Client\connect( $this->websocketUrl );
                
                while ( $message = yield $connection->receive() ) {
                    $payload = yield $message->buffer();
                    
                    $r = $fn( $payload );
                    if ( $r == false ) {
                        $connection->close();
                        break;
                    }
                }
            }
        );
        
        
    }
    
    public function send( object $msg ): void
    {
        global $x;
        $x = $msg;
        
        Loop::run(
            function() {
                global $x;
                $connection = yield Client\connect( $this->websocketUrl );
                yield $connection->send( \json_encode( $x ) );
                yield $connection->close();
                Loop::stop();
            }
        );
    }
}
