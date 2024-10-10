<?php namespace App\Component\Websocket;

use App\Component\Websocket\Client\WebsocketServerClient;
use App\Component\Websocket\Client\WebsocketZmqClient;
use App\Component\Websocket\Client\WebsocketThruwayClient;

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
    
    public function createServerChatClient()
    {
        return new WebsocketServerClient( $this->websocketChatUrl );
    }
    
    public function createServerGameClient()
    {
        return new WebsocketServerClient( $this->websocketGameUrl );
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