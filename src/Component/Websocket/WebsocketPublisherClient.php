<?php namespace App\Component\Websocket;

/**
 * WebsocketClient Based on AMPHP
 * ==============================
 * Reference: https://amphp.org/amp
 * Manual:  https://stackoverflow.com/questions/64292868/how-to-send-a-message-to-specific-websocket-clients-with-symfony-ratchet
 *          https://stackoverflow.com/questions/60780643/get-websocket-pings-from-an-open-stream-connection-using-amp-websocket
 */
final class WebsocketPublisherClient extends AbstractWebsocketClient
{
    public function send( object $msg ): void
    {
        $context = new \ZMQContext();
        $socket = $context->getSocket( \ZMQ::SOCKET_PUSH, 'my publisher' );
        $socket->connect( $this->websocketUrl );
        
        $socket->send( \json_encode( $msg ) );
    }
}
