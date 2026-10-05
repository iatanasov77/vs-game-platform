<?php namespace App\Component\WebsocketSwoole\Websocket;

use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebsocketServer;

interface ServerHandlerInterface
{
    public function onOpen( WebsocketServer $server, Request $request ): void;
    public function onMessage( WebsocketServer $server, Frame $frame ): void;
    public function onClose( WebsocketServer $server, int $fd ): void;
}