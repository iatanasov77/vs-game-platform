<?php namespace App\Component\Websocket\Client;

interface WebsocketClientInterface
{
    public function send( object $msg ): void;
}
