<?php namespace App\Component\Websocket;

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
    
    public function createPublisherClient()
    {
        return new WebsocketPublisherClient( $this->zmqServerUrl );
    }
}