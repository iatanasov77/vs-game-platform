<?php namespace App\Component\Websocket\Client;

use App\Component\Websocket\WebSocketState;

/**
 * WebsocketClient Based on AMPHP
 * ==============================
 * Reference: https://amphp.org/amp
 * Manual:  https://stackoverflow.com/questions/64292868/how-to-send-a-message-to-specific-websocket-clients-with-symfony-ratchet
 *          https://stackoverflow.com/questions/60780643/get-websocket-pings-from-an-open-stream-connection-using-amp-websocket
 */
abstract class AbstractWebsocketClient implements WebsocketClientInterface
{
    /** @var string */
    protected $websocketUrl;
    
    /**
     * Ratchet Connection Resource ID or Any Other Websocket Connection Identifier
     * 
     * @var mixed
     */
    protected $clientId;
    
    /** @var WebSocketState */
    public $State;
    
    public function __construct( string $websocketUrl )
    {
        $this->websocketUrl = $websocketUrl;
        $this->State        = WebSocketState::None;
    }
    
    public function getClientId(): mixed
    {
        return $this->clientId;
    }
    
    public function close(): void
    {
        
    }
}
