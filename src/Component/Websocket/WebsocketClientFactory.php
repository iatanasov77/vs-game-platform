<?php namespace App\Component\Websocket;

use App\Component\Websocket\Client\WebsocketServerClient;
use App\Component\Websocket\Client\WebsocketZmqClient;
use App\Component\Websocket\Client\WebsocketThruwayClient;

final class WebsocketClientFactory
{
    /** @var string */
    private $websocketServerUrl;
    
    /** @var string */
    private $websocketPublisherUrl;
    
    /** @var string */
    private $zmqServerUrl;
    
    public function __construct( string $websocketServerUrl, string $websocketPublisherUrl, string $zmqServerUrl )
    {
        $this->websocketServerUrl       = $websocketServerUrl;
        $this->websocketPublisherUrl    = $websocketPublisherUrl;
        $this->zmqServerUrl             = $zmqServerUrl;
    }
    
    public function createServerClient()
    {
        return new WebsocketServerClient( $this->websocketServerUrl );
    }
    
    public function createZmqClient()
    {
        return new WebsocketZmqClient( $this->zmqServerUrl );
    }
    
    public function createThruwayClient()
    {
        return new WebsocketThruwayClient( $this->websocketPublisherUrl );
    }
}