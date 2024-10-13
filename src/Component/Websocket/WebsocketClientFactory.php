<?php namespace App\Component\Websocket;

use App\Component\Websocket\Client\WebsocketServerClient;
use App\Component\Websocket\Client\WebsocketZmqClient;
use App\Component\Websocket\Client\WebsocketThruwayClient;
use App\Component\Websocket\Client\WebsocketRatchetConnectionClient;

final class WebsocketClientFactory
{
    /** @var string */
    private $websocketChatUrl;
    
    /** @var string */
    private $websocketGameUrl;
    
    /** @var string */
    private $websocketPublisherUrl;
    
    /** @var string */
    private $zmqServerUrl;
    
    public function __construct(
        string $websocketChatUrl,
        string $websocketGameUrl,
        string $websocketPublisherUrl,
        string $zmqServerUrl
    ) {
        $this->websocketChatUrl         = $websocketChatUrl;
        $this->websocketGameUrl         = $websocketGameUrl;
        $this->websocketPublisherUrl    = $websocketPublisherUrl;
        $this->zmqServerUrl             = $zmqServerUrl;
    }
    
    /**
     * Using: Textalk/websocket-php
     *        https://github.com/Textalk/websocket-php
     */
    public function createServerChatClient()
    {
        return new WebsocketServerClient( $this->websocketChatUrl );
    }
    
    /**
     * Using: Textalk/websocket-php
     *        https://github.com/Textalk/websocket-php
     */
    public function createServerGameClient()
    {
        return new WebsocketServerClient( $this->websocketGameUrl );
    }
    
    /**
     * Using: ZMQSocket
     *        https://www.php.net/manual/en/class.zmqsocket.php
     */
    public function createZmqClient()
    {
        return new WebsocketZmqClient( $this->zmqServerUrl );
    }
    
    /**
     * Using: Thruway\Connection
     *        https://github.com/voryx/Thruway.git
     */
    public function createThruwayClient()
    {
        return new WebsocketThruwayClient( $this->websocketPublisherUrl );
    }
    
    /**
     * Using: Ratchet\Connection
     *        https://github.com/voryx/Thruway.git
     */
    public function createRatchetConnectionClient( $connection )
    {
        return new WebsocketRatchetConnectionClient( $this->websocketGameUrl, $connection );
    }
}