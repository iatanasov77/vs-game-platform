<?php namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\EventListener\WebsocketEvent\MessageEvent;
use App\Component\Websocket\WebSocketState;

final class WebsocketEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [MessageEvent::NAME => 'onMessageEvent'];
    }
    
    public function onMessageEvent( MessageEvent $event ): void
    {
        $socket = $event->getWebsocketClient();   
        if ( $socket->State != WebSocketState::Open ) {
            return;
        }
        
        $manager    = $event->getGameManager();
        $text = $event->getWebsocketMessage();
        
        if ( $text != null && ! empty( $text ) ) {
            $event->log( "Received: {$text}" );
            
            try {
                $action = \json_decode( $text );
                $otherClient = $socket == $manager->Client1 ? $manager->Client2 : $manager->Client1;
                
                $manager->DoAction( $action->actionName, $text, $socket, $otherClient );
            } catch ( \Exception $e ) {
                $event->log( "Failed to parse Action text {$e->getMessage()}" );
            }
        }
    }
}
