<?php namespace App\Component\Websocket;

interface WebsocketClientInterface
{
    public function send( object $msg ): void;
}
