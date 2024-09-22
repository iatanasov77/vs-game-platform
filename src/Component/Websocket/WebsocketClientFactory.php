<?php namespace App\Component\Websocket;

final class WebsocketClientFactory
{
    /** @var string */
    private $websocketUrl;
    
    public function __construct( string $websocketUrl )
    {
        $this->websocketUrl = $websocketUrl;
    }
    
    public function createNew()
    {
        return new WebsocketClient( $this->websocketUrl );
    }
}