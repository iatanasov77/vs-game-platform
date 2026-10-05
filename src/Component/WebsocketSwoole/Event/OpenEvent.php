<?php namespace App\Component\WebsocketSwoole\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Swoole\Http\Request;
use App\Component\WebsocketSwoole\Websocket\Server;

class OpenEvent extends Event
{
    public const NAME   = 'Open';
    
    public function __construct( public readonly Server $server, public readonly Request $request )
    {
        // Nothing
    }
}
