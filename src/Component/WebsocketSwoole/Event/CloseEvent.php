<?php namespace App\Component\WebsocketSwoole\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\Component\WebsocketSwoole\Websocket\Server;

class CloseEvent extends Event
{
    public const NAME   = 'Close';
    
    public function __construct( public readonly Server $server, public readonly int $fd )
    {
        // Nothing
    }
}
