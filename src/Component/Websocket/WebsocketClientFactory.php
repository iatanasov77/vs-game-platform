<?php namespace App\Component\Websocket;

use Symfony\Component\Serializer\SerializerInterface;
use App\Component\Websocket\Client\WebsocketServerClient;
use App\Component\Websocket\Client\WebsocketThruwayClient;
use App\Component\Websocket\Client\WebsocketRatchetConnectionClient;

final class WebsocketClientFactory
{
    /** @var SerializerInterface */
    private $serializer;
    
    /** @var string */
    private $websocketChatUrl;
    
    /** @var string */
    private $websocketGameUrl;
    
    public function __construct(
        SerializerInterface $serializer,
        string $websocketChatUrl,
        string $websocketGameUrl
    ) {
        $this->serializer               = $serializer;
        $this->websocketChatUrl         = $websocketChatUrl;
        $this->websocketGameUrl         = $websocketGameUrl;
    }
    
    /**
     * Using: Textalk/websocket-php
     *        https://github.com/Textalk/websocket-php
     */
    public function createServerChatClient()
    {
        return new WebsocketServerClient( $this->websocketChatUrl, $this->serializer );
    }
    
    /**
     * Using: Textalk/websocket-php
     *        https://github.com/Textalk/websocket-php
     */
    public function createServerGameClient()
    {
        return new WebsocketServerClient( $this->websocketGameUrl, $this->serializer );
    }
    
    /**
     * Using: Ratchet\Connection
     *        https://github.com/voryx/Thruway.git
     */
    public function createRatchetConnectionClient( $connection )
    {
        return new WebsocketRatchetConnectionClient( $this->websocketGameUrl, $this->serializer, $connection );
    }
}