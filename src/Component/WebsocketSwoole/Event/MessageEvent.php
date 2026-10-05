<?php namespace App\Component\WebsocketSwoole\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Swoole\WebSocket\Frame;
use App\Component\WebsocketSwoole\Websocket\Server;

class MessageEvent extends Event
{
    public const NAME   = 'Message';
    
    public function __construct( public readonly Server $server, public readonly Frame $frame )
    {
        // Nothing
    }
}
